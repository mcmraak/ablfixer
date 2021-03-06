# ablfixer.php
![ablfixer](https://raw.githubusercontent.com/mcmraak/ablfixer/master/screenshots/ablfixer.png)
###
ablfixer.php - php shell scaner

Скрипт создан для проверки сайта на уязвимости. Рекомендуется использовать скрипт на локальном сервере.

### Особенности

Скрипт быстро и рекурсивно сканирует все файлы сайта, выделяя из общей массы подозрительные.
На подозрительные скрипты с явным вхождением сигнатуры известного шелла, ставит пометку "Опасность".
В момент сканирования отображает прогресс выполнения.

### Дополнительные функции

* Позволяет просматривать, редактировать и удалять подозрительные файлы.
* Записывает все действия в файл отчёта
* Позволяет посмотреть вывод файла (для незащищённых паролем шеллов)

## License

This software is licenced under the [LGPL 2.1](http://www.gnu.org/licenses/lgpl-2.1.html). Please read LICENSE for information on the
software availability and distribution.
