<?php

class Foozball{
    function __construct(){
        $this->db=new PDO('pgsql:host=localhost;dbname=paularnaudo','paularnaudo');
    }
    function check_result($data){
        //store results in an array with user name for easy access
        if($data[1]>$data[3]){
            $results[$data[0]]="WIN";
            $results[$data[2]]="LOSE";
            //Player 1 wins
        }
        elseif($data[3]>$data[1]){
            $results[$data[0]]="LOSE";
            $results[$data[2]]="WIN";
            //Player 2 wins
        }
        else{
            $results[$data[0]]="TIE";
            $results[$data[2]]="TIE";
            //Tie
        }
        return $results;
    }
    function show_scores(){
        //Query DB for scores
        $query="select count(*) as Wins,sum(points) as Points_Scored,name from game_instances i join players p on i.player_id=p.id where i.result='WIN' group by name order by Wins desc";
// Print out everything
        foreach($this->db->query($query) as $row){
            print "User: ".$row['name']." Wins:".$row['wins']."\n";
        }
    }
    function upload_scores($filename){
        $start_time = MICROTIME(TRUE);
        $row=0;
        //Make sure file exists before fopen
        if(!file_exists(trim($filename))){
            print "File doesn't exist sorry!\n";
            exit;
        }
        $handle = fopen(trim($filename), "r");
//take input
        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
            $num_rows = count($data);
            $row++;
            //add our game event to keep things normalized
            $stmt = $this->db->prepare('insert into game (id) values (:id)');
            $stmt->bindParam(':id',$row,PDO::PARAM_INT);
            $stmt->execute();
            $results=[];
            for ($c = 0; $c < $num_rows; $c++) {
                //Hopefully data format stays the same, though we could read headers if we needed to
                if($c==0 || $c==2){
                    /*look to see if our user is in the DB already, if we were going to do one run through every time
                      I would just use a hash map so we don't need to query DB all the time */
                    $stmt = $this->db->prepare('select id from players where name=:name');
                    $stmt->bindParam(':name',$data[$c],PDO::PARAM_STR);
                    $stmt->execute();
                    $user=$stmt->fetch(PDO::FETCH_ASSOC);
                    //No player in DB yet so lets add him/her
                    if(!$user){
                        try{
                            //add our new player
                            $stmt = $this->db->prepare('insert into players (name) values (:name)');
                            $stmt->bindParam(':name',$data[$c],PDO::PARAM_STR);
                            $stmt->execute();
                            $player_id=SELF::get_max_user();
                        } catch(PDOException $e){
                            print "Error!: " . $e->getMessage() . "\n";
                        }

                    }
                    else{
                        //otherwise just take current player
                        $player_id=$user['id'];
                    }
                    $game_id=$row;
                    //figure out who won and lost
                    $results=SELF::check_result($data);
                    //insert the result for our player here
                    $stmt = $this->db->prepare('insert into game_instances (game_id,player_id,points,result) VALUES (:game_id,:player_id,:points,:result)');
                    $stmt->bindParam(':game_id',$game_id,PDO::PARAM_INT);
                    $stmt->bindParam(':player_id',$player_id,PDO::PARAM_INT);
                    $stmt->bindParam(':points',$data[$c+1],PDO::PARAM_INT);
                    $stmt->bindParam(':result',$results[$data[$c]],PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
        }
        //let the user know everything worked
        $stop_time = MICROTIME(TRUE);
        $time = $stop_time - $start_time;
        print "Uploaded $row scores from $filename in $time seconds\n";
    }
    function get_max_user(){
        //for some reason lastinsertID not working so this will have to do
        $stmt = $this->db->query('select last_value from players_id_seq;');
        $last_user_id = $stmt->fetch(PDO::FETCH_ASSOC);
        return $last_user_id['last_value'];
    }
}
?>
