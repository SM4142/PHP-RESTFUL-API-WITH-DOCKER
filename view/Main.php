<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['users']) ? $data['users'][0]["name"] : "de"; ?></title>

</head>
<body>
    
    <p> <?php if(isset($users))   echo $users[0]["name"] ?>  </p>
    <div>
        <? foreach ($users as $user) : ?>
            <p> <?php echo $user['name'] ?> </p>
        <? endforeach ?>
      
    </div>

    <form action="user">
        <input type="text" name="name">
        <button>de</button>
    </form>

    <script id="user-data" type="application/json">
        <?= json_encode($users) ?>
    </script>



    <script src="../utils/js/main.js">
       
    </script>
    
</body>
</html>