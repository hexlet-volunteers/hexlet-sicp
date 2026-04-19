Louis Reasoner wonders why the `simple-query` and `disjoin` procedures (section 4.4.4.2) are implemented using explicit `delay` operations, rather than being defined as follows:

```scheme
(define (simple-query query-pattern frame-stream)
  (stream-flatmap
   (lambda (frame)
     (stream-append (find-assertions query-pattern frame)
                    (apply-rules query-pattern frame)))
   frame-stream))

(define (disjoin disjuncts frame-stream)
  (if (empty-disjunction? disjuncts)
      the-empty-stream
      (interleave
       (qeval (first-disjunct disjuncts) frame-stream)
       (disjoin (rest-disjuncts disjuncts) frame-stream))))
```

Can you give examples of queries where these simpler definitions would lead to undesirable behavior?
