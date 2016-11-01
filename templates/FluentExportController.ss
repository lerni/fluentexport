<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<% base_tag %>
		$MetaTags
		<% require css('framework/css/debug.css') %>
	</head>
	<body>
		<div class="info">
			<h1><% if $Title %>$Title<% else %>Welcome to SilverStripe<% end_if %></h1>
			<% include LangNav %>
		</div>
		<div class="options">
			<% include FluentItemTable %>
			<% include ContentTables %>
		</div>
	</body>
</html>
