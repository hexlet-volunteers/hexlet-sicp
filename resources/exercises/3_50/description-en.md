Complete the following definition, which generalizes `stream-map` to allow procedures that take multiple arguments, analogous to Scheme standart `map` . Scheme standardly provides a generic `map` procedure. This more general map takes a procedure of `n` arguments, together with `n` lists, and applies the procedure to all the first elements of the lists, all the second elements of the lists, and so on, returning a list of the results. For example:

```scheme
(map + (list 1 2 3) (list 40 50 60) (list 700 800 900))
(741 852 963)

(map (lambda (x y) (+ x (* 2 y)))
     (list 1 2 3)
     (list 4 5 6))
(9 12 15)

(define (stream-map proc . argstreams)
  (if ( (car argstreams))
      the-empty-stream
      (
       (apply proc (map  argstreams))
       (apply stream-map
              (cons proc (map  argstreams))))))
```
