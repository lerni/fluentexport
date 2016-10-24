<nav>
    <% loop $FluentClasses %>
		<a class="obj<% if $Top.CurrentItem == $Name %> current<% end_if %>" href="/fluent-export?items={$Name}">{$Name}</a>
    <% end_loop %>
</nav>