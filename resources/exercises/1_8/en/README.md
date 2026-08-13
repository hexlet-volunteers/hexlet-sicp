Newton's method for cube roots is based on the fact that if y is an approximation to the cube root of x, then a better approximation is given by the value

```scheme
x/(y * y) + 2y
--------------
      3
```

Use this formula to implement a `cube-root` procedure analogous to the square-root procedure. In section 1.3.4 we will see how to implement Newton's method in general as an abstraction of these square-root and cube-root procedures.)
