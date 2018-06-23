<!DOCTYPE html>
<html>
<head>
    <title>Page Title</title>
    <style type="text/css">
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 500;
        }

        body {
            background: #f5f5f5;
        }

        form {
            margin: 100px auto 0;
            width: 300px;
            padding: 50px;
            text-align: center;
            background: #fff;
        }

        input, select {
            padding: 10px;
            width: 100%;
            display: block;
            border: 1px #e9e9e9 solid;
            box-sizing: border-box;
        }

        span {
            display: block;
            color: #c00;
        }

        button {
            cursor: pointer;
            color: #fff;
            border: none;
            background: #09c;
            padding: 10px 35px;
        }
    </style>
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.2.1.min.js"></script>
    <script src="~assets/js/formValidation.js"></script>
</head>
<body>

    <?= $BodyInnerSection ?>

</body>
</html>