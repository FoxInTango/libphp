<html>
<head>
<title></title>
<script type="text/javascript">
    /*
    var screen = 
    {
        screen:{width:window.screen.width,height:window.screen.height}
    }
    */
    var newLocation = "?screen=" + '{"' + 'width":'   + '"' + window.screen.width  + '",'
                                        + '"height":' + '"' + window.screen.height + '"}'
                                        + "&options=" + <?php echo json_encode($_GET['options']) ?>;
    //var newLocation = "?token=" + token + "&" + "options=" + JSON.stringify(screen);
    alert(newLocation);
    window.location.replace(newLocation);
</script>
</head>
<body>
</body>
<html>

