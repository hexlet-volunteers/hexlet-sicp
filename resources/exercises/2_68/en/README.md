The `encode` procedure takes as arguments a message and a tree and produces the list of bits that gives the encoded message.

```scheme
(define (encode message tree)
  (if (null? message)
      '()
      (append (encode-symbol (car message) tree)
              (encode (cdr message) tree))))
```

`Encode-symbol` is a procedure, which you must write, that returns the list of bits that encodes a given symbol according to a given tree. You should design `encode-symbol` so that it signals an error if the symbol is not in the tree at all. Test your procedure by encoding the result you obtained in exercise 2.67 with the sample tree and seeing whether it is the same as the original sample message.
