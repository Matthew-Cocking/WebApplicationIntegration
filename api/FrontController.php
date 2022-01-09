<?php
//@author Matthew Cocking (w16015609)
include("config/setenv.php");;

$redirect = $_SERVER['REQUEST_URI']; // Requests the URL
$basePath = "/localhost/"; //Sets a base URL path
$options = array('action' => "", 'subject' => "", 'param1' => "", 'param2' => "", 'param3' => "");
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

//print_r($options);

//
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = explode("/", $path);
if (isset($path[1])) {
    $options['action'] = $path[1];
    if (isset($path[2])) {
        $options['subject'] = $path[2];
        if (isset($path[3])) {
            $options['param1'] = $path[3];
        }
        if (isset($path[4])) {
            $options['param2'] = $path[4];
        }
        if (isset($path[5])) {
            $options['param3'] = $path[5];
        }
    }
}

//Creates variables which stores each part of the URL. E.g. localhost/api/1/2/3 would make $Path1 = api | $path2 = 1 | $path3 = 2 etc
$path1 = $options['action'];
$path2 = $options['subject'];
$path3 = $options['param1'];
$path4 = $options['param2'];
$path5 = $options['param3'];


//Starts a switch case that considers the 1st part of the URL (Each / in the URL is used to seperate the URL into parts)
switch ($path1) {
    case ''  :
    case '/'  :
    case 'index'  :
    $navItems = Array("Home"=>"index", "About"=>"about", "Documentation"=>"documentation",);
    $webpage = new WebPageWithNav("My title", "Home page", $navItems, "Created by Matthew Cocking (w16015609)");

    $webpage->addToBody(
        "This website is an API created for the Web Application Integration Module at Northumbria University <br> 
The Information for this API was taken from an event known as The Conference for Computer Human Interaction hosted in 2019 (CHI2019) <br> 
The website and the creator are in no way affiliated with CHI2019 and is by no means an official website for the event" );

    echo $webpage->getPage();
        break;

    case 'about'  :
        $navItems = Array("Home"=>"index", "About"=>"about", "Documentation"=>"documentation",);
        $webpage = new WebPageWithNav("My title", "Home page", $navItems, "Created by Matthew Cocking (w16015609)");

        $webpage->addToBody( "<h4>This API is in no way associated with the Conference on Human Factors in Computing Systems (CHI)</h4> <br><br><br> 
This API was Created by: Matthew Cocking for an assignment for Web Application Integration Module at Northumbria University <br> 
Student Number: w16015609 <br> Email: Matthew.cocking@northumbria.ac.uk <br><br>" );

        echo $webpage->getPage();
        break;

    case 'documentation'  :
        $navItems = Array("Home"=>"index", "About"=>"about", "Documentation"=>"documentation",);
        $webpage = new WebPageWithNav("My title", "Home page", $navItems, "Created by Matthew Cocking (w16015609)");

        $webpage->addToBody(
            "<h1>API - Endpoints</h1>
          http://localhost/information                                 -  Basic information about the data within the API.<br>
          http://localhost/schedule                                    -  Basic overview of the conference schedule<br>
          http://localhost/api/schedule/:session                       -  Give details of a specific session, including chair and the activities related to the session.<br>
          http://localhost/api/presentations/                          -  Lists all presentations for the conference, and the session it will be presented in. Presentations are ordered by title.<br>
          http://localhost/api/presentations/search/:searchterm        -  Returns the presentations which contain the given search term in the title or abstract.<br>
          http://localhost/api/presentations/categories                -  Returns a list of all the categories that the papers can fall into. <br>
          http://localhost/api/presentations/category/:categoryname    -  Returns the presentations which fall into the specified category. (The category is the type/description)<br>
          
          <h2> Extra API Endpoints used by the Frontend</h2>
          http://localhost/login                                       -  Handles Login requests using JWT <br>
          http://localhost/api/schedule/days                           -  Displays the days that are available. <br>
          http://localhost/api/schedule/:day                           -  Displays the timeslots available on that day. E.g. schedule/Tuesday (must be capital first letter for the day)<br><br>
          The following 2 URLs dont need to be exact, for example the link would ideally look like this:<br>  http://localhost/api/schedule/Tuesday/11/136 <br>
          Currently this URL will show the same results:<br>  http://localhost/api/schedule/fdsj/dfjhdf/136 <br><br>
          http://localhost/api/schedule/:day/:slotsID                  -  Displays sessions within the selected timeslot based on slotsID. <br>
          http://localhost/api/schedule/:day/:slotsID/:sessionsID      -  Displays Information regarding a specified sessions that matches the sessionID specified. <br><br>

" );
        echo $webpage->getPage();
        break;

    case 'information'  :
        header("Content-Type: application/json");
        // Provides basic information about how much information is within the API.
        $sqlQuery = "SELECT COUNT(DISTINCT sessions.id) AS NumberOfSessions, COUNT(papers_authors.activitiesID) AS NumberOfPapers, COUNT(DISTINCT activities.id) AS NumberOfActivities,
        COUNT(DISTINCT authors.authorID) AS NumberOfAuthors, COUNT(DISTINCT papers_awards.activitiesID) AS PapersAwarded, 
        COUNT(DISTINCT slots.id) AS NumberOfTimeslots,
        COUNT(DISTINCT day) AS NumberOfDays FROM sessions
                LEFT JOIN activities
                ON activities.sessionsID = sessions.id
                LEFT JOIN slots
                ON slots.id = sessions.slotsID
                LEFT JOIN papers_awards
                ON papers_awards.activitiesID = activities.id
                LEFT JOIN papers_authors
                ON papers_authors.activitiesID = activities.id
                LEFT JOIN authors
                ON authors.authorID = papers_authors.authorID";
        $response = new JSONRecordSet();
        $response = $response->getJSONRecordSet($sqlQuery);
        echo $response;
        break;
    case 'login' :
        $data = json_decode(file_get_contents("php://input"));

        $email = isset($data->email) ? filter_var($data->email,FILTER_SANITIZE_STRING,FILTER_NULL_ON_FAILURE) : null;
        $password = isset($data->password) ? filter_var($data->password,FILTER_SANITIZE_STRING,FILTER_NULL_ON_FAILURE) : null;
        $loggedIn = false;


        if(!is_null($email) && (!is_null($password))) {
            $dbConn = pdoDB::getConnection();
            $sqlQuery = "SELECT email, password, admin FROM users WHERE email LIKE :email";
            $params = array("email" => $email);
            $queryResult = $dbConn->prepare($sqlQuery);
            $queryResult->execute($params);
            $rows = $queryResult->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                if (password_verify($password, $rows[0]['password']))
                {
                    $token = array();
                    $token['email'] = $email;
                    $encodedToken = JWT::encode($token, ApplicationRegistry::getSecretKey());
                    $loggedIn = true;
                    http_response_code(201);
                    echo json_encode(array("message" => "User Logged in.", "token" => $encodedToken));
                }
                else {
                    http_response_code(201);
                    echo json_encode(array("message" => "Invalid password."));
                    $loggedIn = false;
                }
            }
            else
            {
                http_response_code(201);
                echo json_encode(array("message" => "Account not found."));
                $loggedIn = false;
            }
        }
        else{
            http_response_code(400);
            echo json_encode(array("message" => "Error: Data is incomplete."));
            $loggedIn = false;
        }
        break;
    case'update':
        $data = json_decode(file_get_contents("php://input"));

//$email = isset($data->email) ? filter_var($data->email,FILTER_SANITIZE_STRING,FILTER_NULL_ON_FAILURE) : null;
        $id = isset($data->sessionsID) ? filter_var($data->sessionsID,FILTER_SANITIZE_STRING,FILTER_NULL_ON_FAILURE) : null;
        $chair = isset($data->chair) ? filter_var($data->chair,FILTER_SANITIZE_STRING,FILTER_NULL_ON_FAILURE) : null;
//$token = isset($data->token) ? filter_var($data->token,FILTER_SANITIZE_STRING,FILTER_NULL_ON_FAILURE) : null;

        echo json_encode($id);
        echo json_encode($chair);
//echo json_encode($token);
        try{
            if(!is_null($chair)) {
                $dbConn = pdoDB::getConnection();
                $sql = "UPDATE sessions SET chair= '$chair' WHERE id=$id";
                // Prepare statement
                $stmt = $dbConn->prepare($sql);
                // execute the query
                $stmt->execute();

                // $wasupdated will tell us if anything was updated
                $wasupdated = ($stmt->rowCount() > 0 ? true : false);

                http_response_code(201);
                echo json_encode(array("message" => "database updated", "updated"=>$wasupdated));

            } else {
                http_response_code(400);
                echo json_encode(array("message" => "invalid data", "success"=>false));
            }
        }catch (Exception $e){
            echo "Exception:  " . $e;
            echo $chair;
        }
        break;
    case 'schedule' : //Code to display the whole schedule
        header("Content-Type: application/json");
        $sqlQuery = "SELECT DISTINCT * FROM sessions
                JOIN activities
                ON activities.sessionsID = sessions.id
                JOIN slots
                ON slots.id = sessions.slotsID
                JOIN papers_authors
                ON papers_authors.activitiesID = activities.id
                JOIN authors
                ON authors.authorID = papers_authors.authorID LIMIT 500";
        $response = new JSONRecordSet();
        $response = $response->getJSONRecordSet($sqlQuery);
        echo $response;
        break;
    default:
        echo "The Endpoint entered does not exist please try again";
        break;
    case 'api':
        header("Content-Type: application/json"); //makes the results from this point onwards return in JSON format

        switch ($path2 && !$path3) { //Considers part 2 of the URL and ignores part 3
            case 'presentations' :
                // code to display all presentations shows duplicate presentations because some activities have multiple authors
                // Limited to 250 results to reduce load times.
                $sqlQuery = "SELECT activities.title as activitiesTitle, * FROM activities
                JOIN sessions ON sessions.id = activities.sessionsID
                JOIN papers_authors
                ON papers_authors.activitiesID = activities.id
                JOIN authors
                ON authors.authorID = papers_authors.authorID
                ORDER BY activities.title ASC LIMIT 250";
                $response = new JSONRecordSet();
                $response = $response->getJSONRecordSet($sqlQuery);
                echo $response;
                break;
        }
        switch ($path2) { //consider part 2 of the URL
            case 'presentations' :
                switch ($path3) { //consider part 3 of the URL
                    case 'search' :
                        switch ($path4) {//considers part 4 of the URL
                            case $path4 : //looks for the variable which is stored in the URL and uses it as a search term
                                $sqlQuery = "SELECT DISTINCT *
                                FROM activities
                                JOIN sessions
                                ON activities.sessionsID = sessions.id
                                JOIN slots
                                ON slots.id = sessions.slotsID
                                JOIN papers_awards
                                ON papers_awards.activitiesID = activities.id
                                JOIN papers_authors
                                ON papers_authors.activitiesID = activities.id
                                JOIN authors
                                ON authors.authorID = papers_authors.authorID
                                WHERE abstract LIKE '%$path4%' OR activities.title LIKE '%$path4%'
                                ORDER BY activities.title DESC, papers_authors.authorOrder ASC LIMIT 250";
                                $response = new JSONRecordSet();
                                $response = $response->getJSONRecordSet($sqlQuery);
                                echo $response;
                                break;
                        }
                        break;
                    case 'categories' :
                        switch ($path4) { //displays all categories
                            case $path4 :
                                $sqlQuery = "SELECT DISTINCT type, description FROM sessions";
                                $response = new JSONRecordSet();
                                $response = $response->getJSONRecordSet($sqlQuery);
                                echo $response;
                                break;
                        }
                        break;
                    case 'category' :
                        switch ($path4) { // Looks for activities within a specific category using the Variable stored in the URL
                            case $path4 :
                                $sqlQuery = "SELECT activities.title as activitiesTitle, *
                                FROM activities
                                JOIN sessions
                                ON activities.sessionsID = sessions.id
                                LEFT JOIN papers_awards
                                ON papers_awards.activitiesID = activities.id
                                LEFT JOIN papers_authors
                                ON papers_authors.activitiesID = activities.id
                                LEFT JOIN authors
                                ON authors.authorID = papers_authors.authorID
                                WHERE type == '$path4' OR description == '$path4'
                                ORDER BY activities.title ASC";
                                $response = new JSONRecordSet();
                                $response = $response->getJSONRecordSet($sqlQuery);
                                echo $response;
                                break;
                        }
                }
                break;
            case 'schedule' :
                switch (is_numeric($path3)) { //Checks if part 3 of the URL is an Int and if true continues on with this statement.
                    case $path3 : // selects a specific sessions from the schedule using the ID and displays all of the activites within that session
                        $sqlQuery = "SELECT DISTINCT * FROM sessions
                        JOIN activities
                        ON activities.sessionsID = sessions.id
                        JOIN slots
                        ON slots.id = sessions.slotsID
                        JOIN papers_authors
                        ON papers_authors.activitiesID = activities.id
                        JOIN authors
                        ON authors.authorID = papers_authors.authorID
                        WHERE sessions.id = '$path3'";
                        $response = new JSONRecordSet();
                        $response = $response->getJSONRecordSet($sqlQuery);
                        echo $response;
                        break;
                }

                switch ($path3) {
                    case 'days' : //Gathers information regarding each day there is events on and orders them in weekly order
                        $sqlQuery2 = "SELECT DISTINCT day FROM slots
                        GROUP BY day
                        ORDER BY 
                        CASE day
                        WHEN 'Monday' THEN 1
                        WHEN 'Tuesday' THEN 2
                        WHEN 'Wednesday' THEN 3
                        WHEN 'Thursday' THEN 4
                        END, day";
                        $response = new JSONRecordSet();
                        $response = $response->getJSONRecordSet($sqlQuery2);
                        echo $response;
                        break;
                    case $path3 && !$path4 : // checks part 3 and ignores part 4 of the URL, then selects the timeslots available on the chosen day
                        $sqlQuery2 = "SELECT * FROM slots
                        WHERE day = '$path3'
                        ORDER BY time ASC";
                        $response = new JSONRecordSet();
                        $response = $response->getJSONRecordSet($sqlQuery2);
                        echo $response;
                        break;

                    case $path3 : //checks part 3 of the URL
                        switch ($path4) {
                            case $path4 && !$path5 : //gathers the sessions from the specified timeslot
                                //there is an error where the string 0 doesnt work correctly which causes the results of slotsID = '0' to product not results, however typing "00" in the url will work, not sure on the reason why so don't know how to fix.
                                $sqlQuery = "SELECT *
                                FROM sessions
                                WHERE slotsID = '$path4'";
                                $response = new JSONRecordSet();
                                $response = $response->getJSONRecordSet($sqlQuery);
                                echo $response;
                                break;

                            case $path4 && $path5 : //checks for both part 4 and 5 of the url
                                switch ($path5) {// based on part 5 of the URL execute the query.
                                    //this method has been used throughout the controller however this does mean that the URL doesnt need to be exact
                                    //for example the link ideally should look like this  http://localhost/api/schedule/:day/:timeslot/:sessionID
                                    //however this  http://localhost/api/schedule/fdsj/dfjhdf/:sessionID  would show the same results
                                    case $path5 : //selects all of the activites within the chosen session
                                        $sqlQuery = "SELECT activities.id AS ActivityID, activities.title, chair, room, sessionsID, description, abstract
                                        FROM sessions
                                        JOIN activities 
                                        ON activities.sessionsID = sessions.id
                                        WHERE sessionsID = '$path5'";
                                        $response = new JSONRecordSet();
                                        $response = $response->getJSONRecordSet($sqlQuery);
                                        echo $response;
                                        break;
                                }
                        }
                }
        }
}
?>
