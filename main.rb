# encoding: UTF-8

class Spotifious
	def spotifyQuery(query)
		script = "osascript -e 'tell application \"Spotify\"'"
		script += " -e '#{query}'"
		script += " -e 'end tell'"
		
		`#{script}`
	end
	
	def now
		data = self.spotifyQuery('return name of current track & "✂" & album of current track & "✂" & artist of current track & "✂" & spotify url of current track & "✂" & player state').delete("\n").split "✂"
		headers = [:track, :album, :artist, :url, :state]
		
		return Hash[*headers.zip(data).flatten]
	end
end

# ARGV consists of:
# -	modifier
# -	query

puts