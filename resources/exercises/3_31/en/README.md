The internal procedure `accept-action-procedure!` defined in `make-wire` specifies that when a new action procedure is added to a wire, the procedure is immediately run. Explain why this initialization is necessary. In particular, trace through the `half-adder` example in the paragraphs above and say how the system's response would differ if we had defined accept `accept-action-procedure!` as

```scheme
(define (accept-action-procedure! proc)
  (set! action-procedures (cons proc action-procedures)))
```
