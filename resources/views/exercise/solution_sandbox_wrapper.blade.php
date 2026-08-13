#lang racket
(require racket/sandbox)

;; Решение студента исполняется в песочнице racket/sandbox с жёсткими лимитами:
;;  - sandbox-memory-limit / sandbox-eval-limits ограничивают память и время (CPU),
;;    что убивает бесконечные циклы и попытки исчерпать память (майнеры);
;;  - дефолтный sandbox-security-guard запрещает сеть, запуск процессов (subprocess/system)
;;    и доступ к произвольным файлам;
;;  - sandbox-make-code-inspector отрезает unsafe-операции.
;; Любое нарушение лимита или ошибка кода студента дают exit-код 1 (tests_failed),
;; успешный прогон всех проверок — exit-код 0 (success). Контракт совпадает с App\DTO\CheckResultData.
(with-handlers ([exn:fail:resource? (lambda (e) (printf "~a\n" (exn-message e)) (exit 1))]
                [exn:fail? (lambda (e) (printf "~a\n" (exn-message e)) (exit 1))])
  (parameterize ([sandbox-memory-limit 256]
                 [sandbox-eval-limits (list 10 128)]
                 [sandbox-make-code-inspector make-inspector])
    (make-module-evaluator
     '(module m racket/base
        (require rackunit)
;;; BEGIN
{!! $solution !!}
;;; END
        (define __sicp_tests_failed (box #f))
        (define __sicp_default_check_handler (current-check-handler))
        (current-check-handler
         (lambda (__sicp_failure)
           (set-box! __sicp_tests_failed #t)
           (__sicp_default_check_handler __sicp_failure)))
{!! $tests !!}
        (when (unbox __sicp_tests_failed)
          (error "exercise tests failed"))))
    (exit 0)))
