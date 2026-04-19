Каждая из следующих двух процедур преобразует дерево в список.

```scheme
(define (tree->list-1 tree)
  (if (null? tree)
      '()
      (append (tree->list-1 (left-branch tree))
              (cons (entry tree)
                    (tree->list-1 (right-branch tree))))))

(define (tree->list-2 tree)
  (define (copy-to-list tree result-list)
    (if (null? tree)
        result-list
        (copy-to-list (left-branch tree)
                      (cons (entry tree)
                            (copy-to-list (right-branch tree)
                                          result-list)))))
  (copy-to-list tree '()))
```

а. Для всякого ли дерева эти процедуры дают одинаковый результат? Если нет, то как их результаты различаются? Какой результат дают эти две процедуры для деревьев с рисунка 2.16?

б. Одинаков ли порядок роста этих процедур по отношению к числу шагов, требуемых для преобразования сбалансированного дерева с `n` элементами в список? Если нет, которая из них растет медленнее?

![2.63](/images/exercises/2_63.gif)

Рис. 2.16. Различные бинарные деревья, представляющие множество `{1, 3, 5, 7, 9, 11}` .
