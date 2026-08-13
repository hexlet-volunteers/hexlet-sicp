import {
  useRef, useEffect, useLayoutEffect,
} from 'react'

import { useDispatch, useSelector } from 'react-redux'
import { EditorView } from 'codemirror'
import { history, defaultKeymap, historyKeymap } from '@codemirror/commands'
import { highlightSelectionMatches, searchKeymap } from '@codemirror/search'
import { closeBrackets, autocompletion, closeBracketsKeymap, completionKeymap } from '@codemirror/autocomplete'
import { lintKeymap } from '@codemirror/lint'
import { lineNumbers,
  highlightActiveLineGutter,
  highlightSpecialChars,
  drawSelection,
  dropCursor,
  rectangularSelection,
  crosshairCursor,
  keymap } from '@codemirror/view'
import { foldGutter,
  indentOnInput,
  syntaxHighlighting,
  defaultHighlightStyle,
  bracketMatching,
  foldKeymap } from '@codemirror/language'

import { EditorState } from '@codemirror/state'
import { StreamLanguage } from '@codemirror/language'
import { scheme } from '@codemirror/legacy-modes/mode/scheme'
import { basicDark } from 'cm6-theme-basic-dark'
import { basicLight } from 'cm6-theme-basic-light'
import { useTranslation } from 'react-i18next'
import { changeContent } from '../slices/editorSlice.js'
import codeTemplates from '../common/codeTemplates.js'
import theme from '../common/currentTheme'

const myTheme = theme === 'dark' ? basicDark : basicLight

const basicSetup = (() => [
  lineNumbers(),
  highlightActiveLineGutter(),
  highlightSpecialChars(),
  history(),
  foldGutter(),
  drawSelection(),
  dropCursor(),
  EditorState.allowMultipleSelections.of(true),
  indentOnInput(),
  syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
  bracketMatching(),
  closeBrackets(),
  autocompletion(),
  rectangularSelection(),
  crosshairCursor(),
  highlightSelectionMatches(),
  keymap.of([
    ...closeBracketsKeymap,
    ...defaultKeymap,
    ...searchKeymap,
    ...historyKeymap,
    ...foldKeymap,
    ...completionKeymap,
    ...lintKeymap,
  ]),
])()

const EditorBuilder = () => {
  const { focusesCount } = useSelector((state) => state.editor)
  const { hasTests, preparedCode } = useSelector((state) => state.exerciseInfo)

  const editorRef = useRef()
  const viewRef = useRef()
  const contentRef = useRef()
  const dispatch = useDispatch()
  const { t } = useTranslation()

  const onChange = EditorView.updateListener.of((v) => {
    if (v.docChanged) {
      const value = v.state.doc.toString()
      dispatch(changeContent({ content: value }))
    }
  })

  useLayoutEffect(() => {
    if (hasTests) {
      dispatch(changeContent({ content: codeTemplates.withTemplate(t('editorContent.withTemplate'), preparedCode) }))
      contentRef.current = codeTemplates.withTemplate(t('editorContent.withTemplate'), preparedCode)
    }
    else {
      dispatch(changeContent({ content: codeTemplates.withoutTemplate(t('editorContent.withoutTemplate')) }))
      contentRef.current = codeTemplates.withoutTemplate(t('editorContent.withoutTemplate'))
    }
  }, [dispatch, hasTests, preparedCode, t])

  // biome-ignore lint/correctness/useExhaustiveDependencies: editor is created once on mount; onChange and hasTests are captured intentionally
  useEffect(() => {
    viewRef.current = new EditorView({
      state: EditorState.create({
        doc: contentRef.current,
        extensions: [
          basicSetup,
          StreamLanguage.define(scheme),
          myTheme,
          onChange,
        ],
      }),
      parent: editorRef.current,
    })

    const cursorLocation = hasTests ? contentRef.current.indexOf('\n') + 1 : contentRef.current.length
    viewRef.current.dispatch({ selection: { anchor: cursorLocation } })

    viewRef.current.focus()

    return () => {
      viewRef.current.destroy()
    }
  }, [])

  // biome-ignore lint/correctness/useExhaustiveDependencies: focusesCount is used as an external trigger to re-focus the editor
  useEffect(() => {
    viewRef?.current.focus()
  }, [focusesCount])

  return <div ref={editorRef} />
}

export default EditorBuilder
