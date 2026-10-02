import { Code } from '@mantine/core'
import hljs from 'highlight.js/lib/core'
import scheme from 'highlight.js/lib/languages/scheme'
import 'highlight.js/styles/github.css'
import { useMemo } from 'react'

hljs.registerLanguage('scheme', scheme)

// hljs.highlight — чистая функция над строкой, без DOM: годится для SSR (ADR 0004).
export function CodeBlock({ code }: { code: string }) {
  const html = useMemo(() => hljs.highlight(code, { language: 'scheme' }).value, [code])

  return (
    <Code
      block
      className="hljs"
      style={{ whiteSpace: 'pre-wrap' }}
      // biome-ignore lint/security/noDangerouslySetInnerHtml: highlight.js сам экранирует код, в разметке только его <span class="hljs-*">
      dangerouslySetInnerHTML={{ __html: html }}
    />
  )
}
