<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Http\Request;
use Illuminate\Http\Response;
// encrypt passwords
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return view('instructions');
});

$router->post('/user', function (Request $request) {
  $this->validate($request, [
    'username' => 'required|unique:user',
    'password' => 'required'
  ]);

  DB::insert('insert into user (username, password) values (?, ?)', [$request->input('username'),
    Crypt::encrypt($request->input('password'))
  ]);

  $returnMessage = 'User successfully created!';
  return wrapFormattedResponse([
      'message' => $returnMessage
  ], 200);
});
// Create a new todo note
//  Assign to the logged in user
$router->post('/todonote', function (Request $request) {
  $this->validate($request, [
    'username' => 'required',
    'password' => 'required',
    'content' => 'required'
  ]);

  return authenticateUser(
    $request,
    function(Request $request) {
      DB::insert('insert into todonote (user_id, content, completion_time) values (?, ?, NULL)', [
        (DB::select('select id from user where username is ?', [$request->input('username')]))[0]->id,
        $request->input('content')
      ]);
      $returnMessage = 'TODO note successfully saved!';
      return wrapFormattedResponse([
        'message' => $returnMessage
      ], 200);
    }
  );
});
// Delete a new todo note
//  only on todo notes that the logged in user owns
$router->delete('/todonote', function (Request $request) {
  $this->validate($request, [
    'username' => 'required',
    'password' => 'required',
    'noteid' => 'required'
  ]);

  return authenticateUser(
    $request,
    function(Request $request) {
      $deleted = DB::delete('delete from todonote where user_id = ? and id = ?', [
        (DB::select('select id from user where username is ?', [$request->input('username')]))[0]->id,
        $request->input('noteid')
      ]);
      if ($deleted > 0) {
        $returnMessage = 'The TODO note was successfully deleted!';
        return wrapFormattedResponse([
          'message' => $returnMessage
        ], 200);
      } else {
        $returnMessage = 'TODO note could not be found!';
        return wrapFormattedResponse([
            'message' => $returnMessage
        ], 404);
      }
    }
  );
});
// Mark a todo note as incomplete (set the completion time NULL)
// Mark a todo note as complete (set the completion time to NOW())
//  only on todo notes that the logged in user owns
$router->patch('/todonote', function (Request $request) {
  $this->validate($request, [
    'username' => 'required',
    'password' => 'required',
    'noteid' => 'required',
    'completed' => 'required'
  ]);

  return authenticateUser(
    $request,
    function(Request $request) {
      $completionValue = "NULL";
      if (intval($request->input('completed')) == 1) {
        $completionValue = "date('now')";
      }
      $updated = DB::update("update todonote set completion_time=" . $completionValue . " where user_id = ? and id = ?", [
        (DB::select('select id from user where username = ?', [$request->input('username')]))[0]->id,
        $request->input('noteid')
      ]);
      if ($updated > 0) {
        $returnMessage = 'TODO note was successfully updated!';
        return wrapFormattedResponse([
          'message' => $returnMessage
        ], 200);
      } else {
        $returnMessage = 'TODO note not found';
        return wrapFormattedResponse([
            'message' => $returnMessage
        ], 404);
      }
    }
  );
});
// List all todo notes for the logged in user
$router->post('/todonotes', function (Request $request) {
  $this->validate($request, [
    'username' => 'required',
    'password' => 'required'
  ]);

  return authenticateUser(
    $request,
    function(Request $request) {
      $notes = DB::select('select id, content, completion_time from todonote where user_id = ?', [
        (DB::select('select id from user where username = ?', [$request->input('username')]))[0]->id
      ]);
      return wrapFormattedResponse([
        'data' => $notes
      ], 200);
    }
  );
});
// List all todo notes for an arbitrary user
$router->get('/todonotes/{username}', function (Request $request, $username) {
  $ids = DB::select('select id from user where username = ?', [$username]);
  if (count($ids) == 0) {
    $returnMessage = 'User not found';
    return wrapFormattedResponse([
      'message' => $returnMessage
    ], 404);
  } else {
    $notes = DB::select('select id, content from todonote where user_id = ?', [$ids[0]->id]);
    return wrapFormattedResponse([
      'data' => $notes
    ], 200);
  }
});

function authenticateUser(Request $request, callable $closure) {
  $passwords = DB::select('select password from user where username is ?', [$request->input('username')]);
  if (count($passwords) == 0) {
    return wrapFormattedResponse([
      'message' => 'User not found'
    ], 404);
  }

  try {
    $decrypted = Crypt::decrypt($passwords[0]->password);
    if (strcmp($request->input('password'), $decrypted) !== 0) {
      $returnMessage = 'Failed to authenticate user';
      return wrapFormattedResponse([
        'message' => $returnMessage
      ], 500);
    } else {
      return $closure($request);
    }
  } catch (DecryptException $e) {
    $returnMessage = 'Failed to read database password';
    return wrapFormattedResponse([
        'message' => $returnMessage
    ], 500);
  }
}

function wrapFormattedResponse($data, $statusCode) {
  return response()->json($data, $statusCode);
}

// TODO
// authentication
// frontend
// submit
// encrypt passwords for user table
// put tokens into requests