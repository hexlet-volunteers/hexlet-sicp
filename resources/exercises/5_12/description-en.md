The simulator can be used to help determine the data paths required for implementing a machine with a given controller. Extend the assembler to store the following information in the machine model:

    a list of all instructions, with duplicates removed, sorted by instruction type (
    `assign`
    , 
    `goto`
    , and so on);
    
    a list (without duplicates) of the registers used to hold entry points (these are the registers referenced by 
    `goto`
     instructions);
    
    a list (without duplicates) of the registers that are 
    `save`
    d or 
    `restore`
    d;
    
    for each register, a list (without duplicates) of the sources from which it is assigned (for example, the sources for register 
    `val`
     in the factorial machine of figure 5.11 are 
    `(const 1)`
     and 
    `((op *) (reg n) (reg val))`
    ).
    

Extend the message-passing interface to the machine to provide access to this new information. To test your analyzer, define the Fibonacci machine from figure 5.12 and examine the lists you constructed.

![5.12](/images/exercises/5_12.gif)

```scheme
(controller
   (assign continue (label fact-done))     ; set up final return address
 fact-loop
   (test (op =) (reg n) (const 1))
   (branch (label base-case))
   ;; Set up for the recursive call by saving n and continue.
   ;; Set up continue so that the computation will continue
   ;; at after-fact when the subroutine returns.
   (save continue)
   (save n)
   (assign n (op -) (reg n) (const 1))
   (assign continue (label after-fact))
   (goto (label fact-loop))
 after-fact
   (restore n)
   (restore continue)
   (assign val (op *) (reg n) (reg val))   ; val now contains n(n - 1)!
   (goto (reg continue))                   ; return to caller
 base-case
   (assign val (const 1))                  ; base case: 1! = 1
   (goto (reg continue))                   ; return to caller
 fact-done)
```

Figure 5.11: A recursive factorial machine.

```scheme
(controller
   (assign continue (label fib-done))
 fib-loop
   (test (op
