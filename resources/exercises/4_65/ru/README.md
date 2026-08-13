П.Э. Фект, ожидая собственного продвижения по иерархии, дает запрос, который находит всех шишек (используя правило шишка из раздела 4.4.1):

```scheme
(wheel ?who)
```

К его удивлению, система отвечает

```scheme
;;; Query results:
(wheel (Warbucks Oliver))
(wheel (Bitdiddle Ben))
(wheel (Warbucks Oliver))
(wheel (Warbucks Oliver))
(wheel (Warbucks Oliver))
```

Почему система упоминает Оливера Уорбака четыре раза?
