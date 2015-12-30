This is a bare bones RESTful php app and a angular client app with a mySQL backend.

PROJECT STRUCTURE
The client is in the client folder.  It is made up of the view template and the app controller.
If you are running it locally go to http://localhost/TaskScheduler/client/ to see the app in action.

The RESTful app is in the rest folder.  The config folder contains database parameters.  
The RestServices class is the meat of the server side. It supports exactly 4 methods.  
To make a RESTful call try:

1.  A get request to all the tasks
'GET'   http://localhost/TaskScheduler/rest/task.php      to get array of tasks as json objects

2.  A get request to get one task
'GET'   http://localhost/TaskScheduler/rest/task.php?id=1 to get a single task as a json object by id

3.  A POST request to add a new task
'POST'  http://localhost/TaskScheduler/rest/task.php      to add a new task
{"name":"hire","created":"2015-12-31","interval":"+1 week","description":"hire Andrew"}

4.  A DELETE request to delete a task
'DELETE'http://localhost/TaskScheduler/rest/task.php?id=1 to delete a task by id  

The sql folder contains the sql needed to create the database.


HOW TO INSTALL
1. run the sql file to set up the database.
2. change the rest/config/database.php to your settings
3. put the project folder in you htdocs folder
4. go to http://localhost/TaskScheduler/client/