<!DOCTYPE html>
<html lang="en">
<com:THead ID="titleHeader" Title="<%$ AppTitle %>">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</com:THead>
<body>
    <com:TForm>
        <nav class="navbar navbar-default navbar-inverse navbar-static-top header" role="navigation">
            <com:Application.layout.Header.Header ID="Header" />
        </nav>
        <div class="pageContent container-fluid">
            <com:TContentPlaceHolder ID="MainContent" />
        </div>
		<nav class="footer" role="navigation">
			<com:Application.layout.Footer.Footer ID="Footer" />
		</nav>
    </com:TForm>
</body>
</html>