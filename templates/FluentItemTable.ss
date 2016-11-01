<% if $FluentClasses %>
	<ul class="nav nav-tabs">
		<% loop $FluentClasses %>
			<li class="nav-item<% if $Subitem %> dropdown<% end_if %>">
				<a class="nav-link<% if $Top.CurrentItem == $Name %> active<% end_if %><% if $Subitem %> dropdown-toggle<% end_if %>" <% if $Subitem %> data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"<% end_if %> href="/fluent-export?items={$Name}">{$Name}</a>
			</li>
			<% if $Subitem %>
				<div class="dropdown-menu">
					<% loop $FluentClasses %>
						<a class="dropdown-item" href="/fluent-export?items={$Name}">{$Name}</a>
					<% end_loop %>
				</div>
			<% end_if %>
		<% end_loop %>
	</ul>
<% end_if %>