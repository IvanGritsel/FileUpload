Task 15. Create file upload HTML form and allow users to upload files to the server
Create an upload file field. Perform client-side validation of the file size (use any implementation
you know) and allow only text files and images.
When the file is uploaded, check if its format matches the above file types. If the file does not match the formats, reject it and show a message to the user. If it's not, check if your storage has enough space to save files. If it has not, show a message to the user. If yes, move the file to the 'uploads' folder inside your web root directory. Check if the folder exists. If it does not, create it first. Use '@'to create a directory. Answer in code comment, why you use '@'.
Under the file upload form a list of all files from the folder 'uploads' should be displayed
Get file info: file size, file name, file meta information (for images) and send it to the front-end as
the response of the upload action and show to the user in a user-friendly way.
Also, when downloading each file, you must write a log file. Place the log file in the /logs folder.
Every day it is necessary to create a new log file. The format of the log file name is "upload_ddmmyyyy.log". Information about each attempt to upload a file to the server must be
entered in the log file. Therefore, the log file must contain the Date, Time, file name, file size,
information about whether the file was uploaded successfully (if it is not uploaded, then you
need to write the reason to the log file).
You should implement this application in OOP. Split logic and templates into separate files.
Logic files should contain PHP code only, while templates should contain only HTML and JS.
Use separate CSS files for styles.
For the View part you must use the Twig Template Engine, loaded through Composer
Tags:  PHP, OOP, MVC, Bootstrap, Twig, Composer, HTML, CSS, JS, GIT