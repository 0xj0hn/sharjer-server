<html lang="IR-fa">
    <head>
    <style><?php include "./app/style.css"?></style>
    </head>
    <body dir="rtl">
        <form action="<?=DOMAIN?>/adminpanel/csv_uploader_response/" method="POST" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="نام کاربری"/><br>
            <input type="password" name="password" placeholder="گذرواژه"/><br>
            <input type="file" name="csv_file"/><br>
            <input type="submit" id="button" value="آپلود اکسل" name="submit"/><br>
        </form>
    </body>

</html>
