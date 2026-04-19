Хьюго Дум по ошибке уничтожил в базе данных правило подчиняется (раздел 4.4.1). Обнаружив это, он быстро набивает правило заново, только, к сожалению, по ходу дела вносит небольшое изменение:

```scheme
(rule (outranked-by ?staff-person ?boss)
      (or (supervisor ?staff-person ?boss)
          (and (outranked-by ?middle-manager ?boss)
               (supervisor ?staff-person ?middle-manager))))
```

Сразу после того, как Хьюго ввел информацию в систему, Кон Фиден хочет посмотреть, кому подчиняется Бен Битобор (Ben Bitdiddle). Он вводит запрос

```scheme
(outranked-by (Bitdiddle Ben) ?who)
```

После ответа система проваливается в бесконечный цикл. Объясните, почему.
