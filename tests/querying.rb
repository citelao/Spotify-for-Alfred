# Testing different file write methods.
# Leethal <http://railsforum.com/viewtopic.php?pid=77685#p77685>

def time
  start = Time.now
  yield
  puts Time.now - start
end

time do
	puts
	puts "write var..."
	
	foo = "bar"
end

time do
	puts
	puts "read/write var..."
	
	foo = "bar"
	puts foo
end

time do
	puts
	puts "writing..."
	
	File.open("a.txt","wb") { |f| f.write "foo" }
end

time do
	puts
	puts "marshal write..."

	foo = "bar"
	File.open("b.msh","wb") { |f| Marshal.dump(foo, f) }
end

time do
	puts
	puts "readlines..."
	
	puts File.open("a.txt","rb").readlines[0]
end

time do
	puts
	puts "File.read..."

	puts IO.read("a.txt")
end

time do
	puts
	puts "foreach..."
	
	IO.foreach("a.txt") {|x| puts x }
end

time do
	puts
	puts "each_line..."
	
	File.open("a.txt", "r").each_line { |line| puts line }
end

time do
	puts
	puts "argf..."

	puts ARGF.readlines[0]
end

time do
	puts
	puts "marshal read..."
	
	File.open("b.msh","rb") { |f| puts Marshal.load(f) }
	# puts foo
end