Scheme allows an additional syntax for `cond` clauses, `(&lt;test&gt; => &lt;recipient&gt;)` . If `&lt;test&gt;` evaluates to a true value, then `&lt;recipient&gt;` is evaluated. Its value must be a procedure of one argument; this procedure is then invoked on the value of the `&lt;test&gt;` , and the result is returned as the value of the `cond` expression. For example

```scheme
(cond ((assoc ’b ’((a 1) (b 2))) => cadr)
      (else false))
```

returns `2` . Modify the handling of `cond` so that it supports this extended syntax.
