Story
--
Sometimes I need to quickly take a short note. Existing solution such as OneNote is very heavy and takes alot of time and data to load. Hence, this notes solve my previous problem. It uses MySql database hosted in my own server for storing notes and PDO for querying the database.
![Home page](https://github.com/ArtyumX/Simple-Note/raw/master/1.png)
![Edit note](https://github.com/ArtyumX/Simple-Note/raw/master/2.PNG)

Installation
--
Simply clone the project on your server and access it through your browser.
You will be able to see a nice UI with a form on which you can write anything you want.

If I were to improve this...
--
* ~~Protection using a login form or a simple auth basic.~~
* Set tag for a note.
* Add HTML editor (TinyMCE or something else).
* Delete notes using POST request rather than GET.
* Sort notes by name/date/time.
* Ability to share notes.
* Use AJAX to create/edit/delete notes without having to reload the page.
* JSON API (to use with a Chrome/Firefox extension or a mobile app for example)
* Error handling.
