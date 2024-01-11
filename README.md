1)Скачать архив с апачем  https://disk.yandex.ru/d/ZtECSHhKKzxj-g

2)Закинуть в C:\ . Итоговый путь будет C:\Apache24\

3)Для удобства делаем ярлык на панель для запуска апача (C:\Apache24\bin\httpd.exe).

4)Редачим hosts - 

  1.Открыть блокнот от прав админа через ПКМ.
  
  2.Через блокнот открыть файл c:\windows\system32\drivers\etc\hosts (hosts -это файл без расширения).
  
  3.Добавить в конец строки:
  
    127.0.0.1 testplatform.ru
    
    127.0.0.1 api.testplatform.ru
    
  4.Перезагруть комп обязательно.
  
 5) C testplatform.ru связана папка C:\Apache24\htdocs\site, с  api.testplatorm.ru - C:\Apache24\htdocs\api
  
