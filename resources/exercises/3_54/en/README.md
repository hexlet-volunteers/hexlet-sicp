Define a procedure `mul-streams` , analogous to `add-streams` , that produces the elementwise product of its two input streams. Use this together with the stream of `integers` to complete the following definition of the stream whose `n` th element (counting from `0` ) is `n + 1` factorial:

```scheme
(define factorials (cons-stream 1 (mul-streams  )))
```
