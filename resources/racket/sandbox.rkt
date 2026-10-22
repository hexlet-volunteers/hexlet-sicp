#lang racket
(require racket/sandbox)

;; Использование: racket sandbox.rkt <файл-решения> <файл-тестов>
;;
;; Решение студента читается как данные (read), а не вклеивается в исходник:
;; что бы ни было в тексте, оно становится формами внутри модуля песочницы
;; и исполняется только там (#1936).
;;
;; Решение студента исполняется в песочнице racket/sandbox с жёсткими лимитами:
;;  - sandbox-memory-limit / sandbox-eval-limits ограничивают память и время (CPU),
;;    что убивает бесконечные циклы и попытки исчерпать память (майнеры);
;;  - дефолтный sandbox-security-guard запрещает сеть, запуск процессов (subprocess/system)
;;    и доступ к произвольным файлам;
;;  - sandbox-make-code-inspector отрезает unsafe-операции.
;; Любое нарушение лимита или ошибка кода студента дают exit-код 1 (tests_failed),
;; успешный прогон всех проверок — exit-код 0 (success). Контракт совпадает с App\DTO\CheckResultData.

(define (read-forms path)
  (parameterize ([read-accept-reader #f]
                 [read-accept-lang #f]
                 [read-accept-compiled #f])
    (call-with-input-file path
      (lambda (in)
        (for/list ([form (in-port read in)])
          form)))))

(with-handlers ([exn:fail:resource? (lambda (e) (eprintf "~a\n" (exn-message e)) (exit 1))]
                [exn:fail? (lambda (e) (eprintf "~a\n" (exn-message e)) (exit 1))])
  (define args (current-command-line-arguments))
  (define solution (read-forms (vector-ref args 0)))
  (define tests (read-forms (vector-ref args 1)))
  (parameterize ([sandbox-memory-limit 256]
                 [sandbox-eval-limits (list 10 128)]
                 [sandbox-make-code-inspector make-inspector])
    (make-module-evaluator
     `(module m racket/base
        (require rackunit)
        ,@solution
        (define __sicp_tests_failed (box #f))
        (define __sicp_default_check_handler (current-check-handler))
        (current-check-handler
         (lambda (__sicp_failure)
           (set-box! __sicp_tests_failed #t)
           (__sicp_default_check_handler __sicp_failure)))
        ,@tests
        (when (unbox __sicp_tests_failed)
          (error "exercise tests failed"))))
    (exit 0)))
