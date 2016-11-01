<% if $showItems %>
	<% loop $showItems %>
	<% if $Me %>
		<% loop $Me %>
			<% if $Me %>
				<% loop $Me %>
					<% if $Up.Pos == 2 && $Pos == 2 %>
						<h2>$item</h2>
					<% end_if %>
				<% end_loop %>
			<% end_if %>
		<% end_loop %>
	<% end_if %>
	<table class="table table-bordered table-striped">
		<tbody>
			<% if $Me %>
				<% loop $Me %>
					<tr>
						<% if $Me %>
							<% loop $Me %>
								<% if $Up.Pos == 1 %>
									<th>$item</th>
								<% else %>
									<td>$item</td>
								<% end_if %>
							<% end_loop %>
						<% end_if %>
					</tr>
				<% end_loop %>
			<% end_if %>
		</tbody>
	</table>
	<% end_loop %>
<% end_if %>