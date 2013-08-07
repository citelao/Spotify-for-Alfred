class Alfredify
	def initialize
		@items = Array.new
	end
	
	def add(item = {})
		# TODO test only subtitle
		item[:title] = "null" if item[:title].nil?
		
		raise "invalid argument for 'valid'" unless item[:valid].nil? || item[:valid].is_vague_boolean?
		
		raise "'autocomplete' is required when 'valid' is 'no'" if item[:valid] == "no" && item[:autocomplete].nil?
	
		@items.push item
	end
	
	def output!
		raise "no items" if @items.to_a.empty?
		
		out = '<?xml version="1.0"?>\r\n'
		out += '\t<items>\r\n'
		
		@items.each do |item| 
			out += "\t\t<item"
			out += " uid='#{item[:uid]}'" unless item[:uid].nil?
			out += " arg='#{item[:arg]}'" unless item[:arg].nil?
			out += " valid='#{item[:valid]}'" unless item[:valid].nil?
			out += " autocomplete='#{item[:autocomplete]}'" unless item[:autocomplete].nil?
			out += ">\r\n"
			
			out += "\t\t\t<title>#{item[:title]}</title>\r\n"
			out += "\t\t\t<subtitle>#{item[:subtitle]}</subtitle>\r\n" unless item[:subtitle].nil?
			out += "\t\t\t<icon>#{item[:icon]}</icon>\r\n" unless item[:icon].nil?
			out += "\t\t</item>\r\n"
		end
		
		out += "\t</items>"
		
		out
	end
end

class String
	def is_vague_boolean?
		return true if self == "yes" || self == "no"
		false
	end
end