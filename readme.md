# Logger U7

Logging a data for debug code in WordPress


For adding data in log use hook:

## Simple example
```
$var_data = 'test';
do_action("logger_u7", $var_data);
```

## Example with multiple var for log and labels

```
$var_data1 = 'test1';
do_action("logger_u7", ['tag1', $var_data1]);

$var_data2 = 'test2';
do_action("logger_u7", ['tag2', $var_data2]);
```

## How get debug data?

After debug data can view log in admin: Tools / Logger

## Changelog

= 1.3 =
* Добавлена кнопка очистки данных
* Добавлена дополнительная колонка со счетчиком по порядку
* Обновлена функция вывода данных
* Добавлено удаление данных при удалении плагина

= 1.2 =
* Init commit

