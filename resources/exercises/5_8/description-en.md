The following register-machine code is ambiguous, because the label `here` is defined more than once:

```scheme
start
  (goto (label here))
here
  (assign a (const 3))
  (goto (label there))
here
  (assign a (const 4))
  (goto (label there))
there
```

With the simulator as written, what will the contents of register a be when control reaches `there` ? Modify the `extract-labels` procedure so that the assembler will signal an error if the same label name is used to indicate two different locations.
