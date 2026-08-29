<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 
    

     if(isset($_POST['conn']))
    {
        $host = "localhost";
        $user = "root";
        $pass = "";
        $db = "ecommerce";

        @$conn = mysqli_connect($host, $user, $pass, $db);

         if($conn){
            echo "Yes, connected";
         }else{
            die('No, there are errors') ;

          // echo "No, there are errors";
         }

        //  $sql = "insert into products(category_id, name, description, price, quantity, image)
        //     values(1, 'Car Wheel', 'Fashion', 200, 1, 'carwheel.jpg')";

         //$sql = "update products set category_id = 4 where id = 6"; 
        
        //  $sql = "insert into categories(name)
        //     values('Cars')";

            $sql = "create table if not exists Nasr(id int primary key auto_increment,
            name varchar(100) unique not null )";
            
            $result = mysqli_query($conn, $sql);

            if($result){
                echo "Updated successfully";
            }else{
                echo "Data not added yet";
            }
    }
     ?>


   
     <form action="" method="post">
        <center>
            <input type="submit" value="conn" name="conn"> 
        </center>
     </form>

</body>
</html>