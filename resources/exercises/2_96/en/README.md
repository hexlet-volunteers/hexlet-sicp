a. Implement the procedure `pseudoremainder-terms` , which is just like `remainder-terms` except that it multiplies the dividend by the integerizing factor described above before calling `div-terms` . Modify `gcd-terms` to use `pseudoremainder-terms` , and verify that `greatest-common-divisor` now produces an answer with integer coefficients on the example in exercise 2.95

b. The GCD now has integer coefficients, but they are larger than those of `P1` . Modify `gcd-terms` so that it removes common factors from the coefficients of the answer by dividing all the coefficients by their (integer) greatest common divisor.

Thus, here is how to reduce a rational function to lowest terms:

    Compute the GCD of the numerator and denominator, using the version of 
        `gcd-terms`
         from exercise 
        2.96
    
    When you obtain the GCD, multiply both numerator and denominator by the same integerizing factor before dividing through by the GCD, so that division by the GCD will not introduce any noninteger coefficients. As the factor you can use the leading coefficient of the GCD raised to the power 
        `1 + O1 − O2`
        , where 
        `O2`
         is the order of the GCD and 
        `O1`
         is the maximum of the orders of the numerator and denominator. This will ensure that dividing the numerator and denominator by the GCD will not introduce any fractions.
    
    The result of this operation will be a numerator and denominator with integer coefficients. The coefficients will normally be very large because of all of the integerizing factors, so the last step is to remove the redundant factors by computing the (integer) greatest common divisor of all the coefficients of the numerator and the denominator and dividing through by this factor.
