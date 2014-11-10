<?php
/*
 * DB Schema:
 *  Column |         Type          |                      Modifiers
--------+-----------------------+------------------------------------------------------
 id     | integer               | not null default nextval('players_id_seq'::regclass)
 name   | character varying(50) |
Indexes:
    "players_pkey" PRIMARY KEY, btree (id)

                                 Table "public.game_instances"
  Column   |         Type          |                          Modifiers
-----------+-----------------------+-------------------------------------------------------------
 id        | integer               | not null default nextval('game_instances_id_seq'::regclass)
 game_id   | integer               | not null
 player_id | integer               |
 points    | integer               |
 result    | character varying(10) |
Indexes:
    "game_instances_pkey" PRIMARY KEY, btree (id)

     Table "public.game"
 Column |  Type   | Modifiers
--------+---------+-----------
 id     | integer | not null
Indexes:
    "game_pkey" PRIMARY KEY, btree (id)
 */

include_once 'lib.php';
print "Welcome to the Foosballtron tracking system, make your selection:\n";
print "1) Upload Scores\n";
print "2) See Leaderboard\n";
$choice = fgets(STDIN);
$foosball=new Foozball();
switch($choice){
    case 1:
        //Upload scores here
        print "Please input filename:\n";
        $filename=fgets(STDIN);
        $foosball->upload_scores($filename);
        break;
    case 2:
        //see the boss of Foosball
        $foosball->show_scores();

}




?>