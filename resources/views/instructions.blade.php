<html>
  <body>
    <form name="create-user-form" method='POST' action='http://localhost:8000/username'>
      <h1>Create a user</h1>
      <span>Username</span>
      <input type="text" name="username" />
      <span>Password</span>
      <input type="password" name="password"/>
      <br />
      <input type="submit" name="Create user" value="submit" />
    </form>
    <form name="create-todonote-form" method='POST' action='http://localhost:8000/todonote'>
      <h1>Create a TODO note</h1>
      <span>Username</span>
      <input type="text" name="username" />
      <span>Password</span>
      <input type="password" name="password"/>
      <br />
      <span>Content</span>
      <br />
      <input type="text" name="content" />
      <br />
      <input type="submit" name="Create TODO note" value="submit" />
    </form>
    <div id="delete-todonote-form">
      <h1>Delete a TODO note</h1>
      <span>Username</span>
      <input id="delete-todonote-username" type="text" name="username" />
      <span>Password</span>
      <input id="delete-todonote-password" type="password" name="password"/>
      <br />
      <span>Note ID</span>
      <br />
      <input id="delete-todonote-noteid" type="number" name="noteid" />
      <br />
      <button onclick="deleteTodoNote()">Delete TODO note</button>
    </div>
    <div id="mark-todonote-form">
      <h1>Mark a TODO note</h1>
      <span>Username</span>
      <input id="mark-todonote-username" type="text" name="username" />
      <span>Password</span>
      <input id="mark-todonote-password" type="password" name="password"/>
      <br />
      <span>Note ID</span>
      <br />
      <input id="mark-todonote-noteid" type="number" name="noteid" />
      <br />
      <span>Completed</span>
      <input type="checkbox" id="mark-todonote-completed" />
      <button onclick="markTodoNote()">Mark TODO note</button>
    </div>
    <form name="get-todonotes-form" method="post" action="http://localhost:8000/todonotes">
      <h1>Get a TODO notes</h1>
      <span>Username</span>
      <input type="text" name="username" />
      <span>Password</span>
      <input type="password" name="password"/>
      <br />
      <input type="submit" value="submit" name="Get TODO notes" />
    </form>
    <div id="get-todonotesforuser-form">
      <h1>Get a TODO notes</h1>
      <span>Username</span>
      <input type="text" id="get-todonotesforuser-username" />
      <br />
      <button onclick="getTodoNotesForUser()">Get TODO notes</button>
    </div>
    
    <script>
      function deleteTodoNote() {
        const url = new URL('http://localhost:8000/todonote');
        fetch(url, {
          method:'DELETE',
          body:JSON.stringify({
            "username": document.getElementById("delete-todonote-username").value,
            "password": document.getElementById("delete-todonote-password").value,
            "noteid": document.getElementById("delete-todonote-noteid").value,
          }),
          headers: {
            'Content-Type': 'application/json;charset=utf-8'
          }
        }).then(function(response) {
          console.log(response);
        });
      }
      function markTodoNote() {
        const url = new URL('http://localhost:8000/todonote');
        fetch(url, {
          method:'PATCH',
          body:JSON.stringify({
            "username": document.getElementById("mark-todonote-username").value,
            "password": document.getElementById("mark-todonote-password").value,
            "noteid": document.getElementById("mark-todonote-noteid").value,
            "completed": document.getElementById("mark-todonote-completed").value == "on" ? 1 : 0,
          }),
          headers: {
            'Content-Type': 'application/json;charset=utf-8'
          }
        }).then(function(response) {
          console.log(response);
        });
      }
      function getTodoNotesForUser() {
        const givenUsername = document.getElementById("get-todonotesforuser-username").value;
        const url = new URL('http://localhost:8000/todonotes/'+givenUsername);
        fetch(url).then(function(response) {
          console.log(response);
          alert(response);
        });
      }
    </script>
  </body>
</html>