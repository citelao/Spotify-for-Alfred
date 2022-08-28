# Add Homebrew to path for those who need :)
export PATH="/usr/local/bin:/opt/homebrew/bin:${PATH}"

# Show PHP installation instructions if needed.
if ! command -v php &> /dev/null
then
	sh before_php_installed.sh
	exit
fi

php -f main.php -- "$1"