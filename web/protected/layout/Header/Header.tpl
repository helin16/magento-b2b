<div class="headerDiv">
	<span class="welcome_user inlineblock">
		Welcome, <a href='/me.html'><%= Core::getUser()->getPerson() %></a>
		[<com:TLinkButton Text="Logout" onClick="logout" CssClass="logoutBtn"/>]
	</span>
</div>