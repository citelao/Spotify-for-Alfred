# Add Homebrew to path for those who need :)
export PATH="/usr/local/bin:${PATH}"

# If no PHP installed, exit with no action.
if ! command -v php &> /dev/null
then
	exit
fi

php -f action.php -- $1