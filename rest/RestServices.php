<?php

class RestServices {

    private $mysqli;
    
    /*
     * The HTTP request method
     */
    private $method;
    
    /*
     * The id of the task if provided in the query string (eg task.php?id=1)
     */
    private $id = NULL;

    public function __construct($db) {
        //Connect to the database
        $this->mysqli = new mysqli($db['host'], $db['user'], $db['password'], $db['name']);
        // Check connection
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }
        
        //save the request method
        $this->method = $_SERVER['REQUEST_METHOD'];
        
        //save the id if it is in the query string
        if(!empty($_GET) && isset($_GET['id'])){
            $this->id = $_GET['id'];
        }
    }

    /**
     * The entry method into the REST app
     * This routes the request to the correct function
     */
    public function handle() {
        header('Content-Type: application/json');
        switch ($this->method) {
            case 'POST':
                echo $this->post();
                break;

            case 'GET':
                if (isset($this->id))
                    echo $this->read();
                else
                    echo $this->get();
                break;

            case 'DELETE':
                echo $this->delete();
                break;
            
            default:
                $this->methodNotAllowed();
                break;
        }
    }

    /*
     * Close the database connection
     */
    public function close() {
        return $this->mysqli->close();
    }

    /*
     * Get all the tasks
     */
    public function get() {

        $query = "select id, name, created, due, description from tasks";

        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $arr = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr[] = $row;
            }
        }

        return json_encode($arr);
    }

    /*
     * Get an individual task by id
     */
    public function read($id) {
        $stmt = $this->mysqli->prepare('Select `id`, `name`, `created`, `due`, `description` from `tasks` where `id` = ?');

        if (!$stmt->bind_param('i', $id))
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;

        if (!$stmt->execute())
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;

        $row = array();
        $stmt->bind_result($id, $row['name'], $row['created'], $row['due'], $row['description']);

        if (!$stmt->fetch())
            $row = NULL;
        $stmt->close();
        return json_encode($row);
    }

    /*
     * Add a task
     */
    public function post() {
        $data = json_decode(file_get_contents("php://input"));
        
        //basic data validation
        $valid = TRUE;
        if(!isset($data->name) || strlen($data->name) < 1)
            $valid = FALSE;
        if(!isset($data->description) || strlen($data->description) < 1)
            $valid = FALSE;
        if(!isset($data->created) || !date_create($data->created))
            $valid = FALSE;
        if(!isset($data->interval) || !in_array($data->interval, ['+1 week', '+1 month', '+1 year']))
            $valid = FALSE;
        
        if(!$valid){
            $this->badRequest();
            return;
        }
        
        $due = new DateTime($data->created);
        $due->modify($data->interval);

        $stmt = $this->mysqli->prepare('INSERT INTO tasks (name, created, due, description) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $data->name, $data->created, $due->format('Y-m-d'), $data->description);
        $stmt->execute();
        $id = $this->mysqli->insert_id;
        $stmt->close();
        $this->created();
        
        $new_task = array(
            'id'=>$id, 'name'=>$data->name, 'created'=>$data->created, 
            'due'=>$due->format('Y-m-d'), 'description'=>$data->description
        );
        return json_encode($new_task);
    }

    /*
     * Delete a task by id
     */
    public function delete() {
        if(!isset($this->id)){
            $this->badRequest();
            return;
        }
        
        $stmt = $this->mysqli->prepare('Delete from `tasks` where `id` = ?');
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        if($stmt->affected_rows<1)
            $this->notFound();
        
        $stmt->close();
        return json_encode(TRUE);
    }

    function created() {
        header('HTTP/1.0 201 Created');
    }

    function badRequest() {
        header('HTTP/1.0 400 Bad Request');
    }

    function notFound() {
        header('HTTP/1.0 404 Not Found');
    }

    function methodNotAllowed() {
        header('HTTP/1.0 405 Method Not Allowed');
        header('Allow: GET, POST, DELETE');
    }
    

}
