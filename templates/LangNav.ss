<ul class="langNav">
	<% loop $Locales %>
		<li class="$LinkingMode $FirstLast" >
			<a href="$Link.ATT" title="$Title.XML" <% if $LinkingMode != 'invalid' %>rel="alternate" hreflang="$LocaleRFC1766"<% end_if %>>
				$Locale
			</a>
		</li>
	<% end_loop %>
</ul>