<!DOCTYPE html>
<html lang="en">
<com:THead ID="titleHeader" Title="<%$ AppTitle %>">
    <meta charset="UTF-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="description" content="">
	<meta name="keywords" content="">
</com:THead>
<body>
    <com:TForm>
	    <div id="frontend">
	        <div class="framewrapper header">
	            <div class="contentwrapper">
		            <com:Application.layout.Header.Header ID="Header" />
			        <com:Application.layout.Menu.Menu ID="Menu" />
	            </div>
	        </div>
	        <div class="framewrapper content">
	            <div class="contentwrapper">
	               <com:TContentPlaceHolder ID="MainContent" />
	            </div>
	        </div>
	        <div class="framewrapper footer">
	            <div class="contentwrapper">
	                <com:Application.layout.Footer.Footer ID="Footer" />
	            </div>
	        </div>
	    </div>
    </com:TForm>
</body>
</html>