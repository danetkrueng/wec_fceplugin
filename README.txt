current requirements:
	- page can not be cached
	- there must be a link around the linkable area of the list template.
		- the href of that link must be mapped to lib.detaillink
	- Include static (from extensions):
		add "Generic FCE Plugin"
			which includes this typoscript setup:
				lib.detaillink = TEXT
				lib.detaillink.value = ###DETAILLINK###
reasons:
	- $this->pi_getPageLink does not allow for caching
	- str_replace('###DETAILLINK###'... is used to do insert the link into the template.


BASIC SETUP
Create an FCE DS and template object for the detail view.
	- add an href attribute, but don't map it.
Create a new template object for the same DS, but make it for the list view.
	- fewer fields
	- map the href field