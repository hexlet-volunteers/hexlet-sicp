Следующая процедура `list->tree` преобразует упорядоченный список в сбалансированное бинарное дерево. Вспомогательная процедура `partial-tree` принимает в качестве аргументов целое число `n` и список по крайней мере из `n` элементов, и строит сбалансированное дерево из первых `n` элементов дерева. Результат, который возвращает `partial-tree` , — это пара (построенная через `cons` ), `car` которой есть построенное дерево, а `cdr` — список элементов, не включенных в дерево.

```scheme
(define (list->tree elements)
  (car (partial-tree elements (length elements))))

(define (partial-tree elts n)
  (if (= n 0)
      (cons '() elts)
      (let ((left-size (quotient (- n 1) 2)))
        (let ((left-result (partial-tree elts left-size)))
          (let ((left-tree (car left-result))
                (non-left-elts (cdr left-result))
                (right-size (- n (+ left-size 1))))
            (let ((this-entry (car non-left-elts))
                  (right-result (partial-tree (cdr non-left-elts)
                                              right-size)))
              (let ((right-tree (car right-result))
                    (remaining-elts (cdr right-result)))
                (cons (make-tree this-entry left-tree right-tree)
                      remaining-elts))))))))
```

а. Дайте краткое описание, как можно более ясно объясняющее работу `partial-tree` . Нарисуйте дерево, которое `list->tree` строит из списка `(1 3 5 7 9 11)`

б. Каков порядок роста по отношению к числу шагов, которые требуются процедуре `list->tree` для преобразования дерева из `n` элементов?
