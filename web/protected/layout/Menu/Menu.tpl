<div class="systemtitle">
    <ul class="mainMenu">
        <li class="mainMenuItem"><a href="/">Home</a></li>
        <li class="mainMenuItem"><com:TLinkButton Text="Logout" onClick="logout"/></li>
    </ul>
    <span class="welcome_user inlineblock">Welcome, <%= Core::getUser() instanceof UserAccount ? "<a href='/user.html'>" . Core::getUser()->getPerson() . "</a>" : 'Guest' %></span>
</div>