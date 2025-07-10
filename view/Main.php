<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><? isset($data['users']) ? $data['users'][0]["name"] : "de"  ?> </title>

</head>
<body>
    
    <p> <?php if(isset($data['users']))   echo $data['users'][0]["name"] ?>  </p>
    <div>
        <? foreach ($data['users'] as $user) : ?>
            <p> <?php echo $user['name'] ?> </p>
        <? endforeach ?>
      
    </div>
</body>
</html>