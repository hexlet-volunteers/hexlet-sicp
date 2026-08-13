Define `P₁` , `P₂` , and `P₃` to be the polynomials

```scheme
P1: x2 − 2x + 1
P2: 11x2 + 1
P3: 13x + 5
```

Now define `Q1` to be the product of `P₁` and `P₂` and `Q₂` to be the product of `P₁` and P₃ `P₃` , and use `greatest-common-divisor` (exercise 2.94 ) to compute the GCD of `Q₁` and `Q₂` . Note that the answer is not the same as `P₁` . This example introduces noninteger operations into the computation, causing difficulties with the GCD algorithm. To understand what is happening, try tracing `gcd-terms` while computing the GCD or try performing the division by hand.

We can solve the problem exhibited in exercise 2.95 if we use the following modification of the GCD algorithm (which really works only in the case of polynomials with integer coefficients). Before performing any polynomial division in the GCD computation, we multiply the dividend by an integer constant factor, chosen to guarantee that no fractions will arise during the division process. Our answer will thus differ from the actual GCD by an integer constant factor, but this does not matter in the case of reducing rational functions to lowest terms; the GCD will be used to divide both the numerator and denominator, so the integer constant factor will cancel out.

More precisely, if `P` and `Q` are polynomials, let `O₁` be the order of `P` (i.e., the order of the his largest term ) and let `O₂` be the order of `Q` . Let `c` be the leading coefficient of `Q` . Then it can be shown that, if we multiply `P` by the integerizing factor `c^(1 + O₁ − O₂)` , the resulting polynomial can be divided by `Q` by using the `div-terms` algorithm without introducing any fractions. The operation of multiplying the dividend by this constant and then dividing is sometimes called the pseudodivision of `P` by `Q` . The remainder of the division is called the pseudoremainder.
