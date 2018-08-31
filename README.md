**Test app**

Application provides REST API for messages

**Authentification**

All request for _message_ resources required user authorization, provided by acces token in request header

**Format:**

`Authorization: Bearer
  eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHR
  wczovL2FjY291bnRzLmFjLnVhL2F1dGgvbG9naW4iLCJpYXQiOjE
  0OTQyNDExODMsImV4cCI6MTQ5NDI2Mjc4MywibmJmIjoxNDk0MjQ
  xMTgzLCJqdGkiOiJ2YjY0RFd2TUpZbnlETDZnIiwic3ViIjoxfQ.
  _3g85ZyzwZnbJLMSO7tG6mrfgMfeena4_NwCQWS8UDI`
  
**Methods**

**Sign up new user:**
Creates new user and return access token

POST /user/signup

_Params:_

`phoneNumber - integet required`

`email       - email   required`

`password    - string  required`

_Response:_

`{
 "success": true,
 "data": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hcGl0ZXN0LmxvY2FsIiwiYXVkIjoiaHR0cDpcL1wvYXBpdGVzdC5sb2NhbCIsImlhdCI6MTUzNTcwMjE0MiwiZXhwIjoxNTM1NzAyNzQyLCJuYmYiOjE1MzU3MDIxNDIsImp0aSI6M30.O9STh4MtreJMCpmrRjc-uLecDe1TMfc7E0E8HW-CUyg"
 }`
 
 
**User login:**
Check user password and return access token

POST /user/login

_Params:_

`email       - email   required`

`password    - string  required`

_Response:_

`{
 "success": true,
 "data": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hcGl0ZXN0LmxvY2FsIiwiYXVkIjoiaHR0cDpcL1wvYXBpdGVzdC5sb2NhbCIsImlhdCI6MTUzNTcwMjE0MiwiZXhwIjoxNTM1NzAyNzQyLCJuYmYiOjE1MzU3MDIxNDIsImp0aSI6M30.O9STh4MtreJMCpmrRjc-uLecDe1TMfc7E0E8HW-CUyg"
 }`
 
 
**Send message:**
Send message to another user

POST /message

_Params:_

`email       - email   required`

`title       - string  required`

`text        - text  required`

 
**Get messages:**
Get users messages.
Type - filter by receiver or sender
Status- filter by delivering status (sent - both)

GET /message

_Params:_


`type       - string  required (sent, received)`

`status     - string  required (sent, delivered, received)`


 
**Delete message:**
Delete user messages

DELETE /message/{id}

_Params:_


`id       - int  required (message id)`