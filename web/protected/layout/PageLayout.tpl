<!DOCTYPE html>
<html lang="en">
<com:THead ID="titleHeader" Title="<%$ AppTitle %>">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
	<!-- Latest compiled and minified JavaScript -->
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		/*<![CDATA[*/
		jQuery.noConflict();
		/*]]>*/
	</script>
</com:THead>
<body>
    <com:TForm>
        <nav class="navbar navbar-default navbar-inverse navbar-static-top header" role="navigation">
            <com:Application.layout.Header.Header ID="Header" />
        </nav>
        <div class="content container">
            <com:TContentPlaceHolder ID="MainContent" />
        </div>
		<nav class="footer" role="navigation">
			<com:Application.layout.Footer.Footer ID="Footer" />
		</nav>
        </div>
    </com:TForm>
</body>
</html>