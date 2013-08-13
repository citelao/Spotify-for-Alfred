require_relative '../alfredify'
require 'xmlsimple'

RSpec::Matchers.define :send_exact_output do |expected|
	match do |actual|
		XmlSimple.xml_in(actual) == expected
	end
	
	failure_message_for_should do |actual|
		
		a = XmlSimple.xml_in(actual).pretty_inspect
		b = expected.pretty_inspect
		
		
		"expected XML response to be: \r\n #{b} \r\n received: \r\n #{a}"
	end
end

RSpec::Matchers.define :send_output do |e|
	match do |actual|
		a = XmlSimple.xml_in(actual) 
		
		a.merge(e) == a
	end

	failure_message_for_should do |actual|

		a = XmlSimple.xml_in(actual).pretty_inspect
		b = e.pretty_inspect
		c = XmlSimple.xml_in(actual).merge(e).pretty_inspect

		"expected XML response to be: \r\n #{b} \r\n received: \r\n #{a} \r\n combined: \r\n #{c}"
	end
end

describe "alfredify" do
	before(:each) do
		@a = Alfredify.new()
	end

	context "when validating" do
		it "should output all legitimate passed variables" do
			@a.add(
				:title => 'title',
				:subtitle => 'subtitle',
				:icon => 'icon',
				:uid => 'uid',
				:arg => 'arg',
				:autocomplete => 'autocomplete',
				:valid => 'yes'
			)

			expect(@a.output!).to send_output({"item" => [{
					"title" => ["title"],
					"subtitle" => ["subtitle"],
					"icon" => ["icon"],
					"uid" => "uid",
					"arg" => "arg",
					"autocomplete" => "autocomplete",
					"valid" => "yes"
			}]})
		end
		
		it "should require a title or subtitle" do
			expect(@a).to receive(:warn).with("must have title or subtitle")
			@a.add
			
			expect(@a).not_to receive(:warn).with("must have title or subtitle")
			@a.add :title => 'works'
			@a.add :subtitle => 'works'
		end

		it "should require valid to be boolean" do
			expect(@a).to receive(:warn).with("invalid argument for 'valid'")
			@a.add :title => 'works', :valid => 'dumb'
		end

		it "should require autocomplete if invalid" do
			expect(@a).to receive(:warn).with("'autocomplete' is required when 'valid' is 'no'")
			@a.add :title => 'works', :valid => 'no'
		end
		
		it "should require items to output" do
			expect(@a).to receive(:warn).with("no items to output")
			@a.output!
		end
	end

	context "when outputting" do
		it "should output alfred xml" do
			@a.add :title => 'title'

			expect(@a.output!).to send_output({"item" => [
				{"title" => ["title"]}
			]})
		end

		it "should allow multiple items" do
			@a.add :title => 'title 1'
			@a.add :title => 'title 2'
			
			expect(@a.output!).to send_output({"item" => [
				{"title" => ["title 1"]}, 
				{"title" => ["title 2"]}
			]})
		end

		it "should throw internal errors nicely"
		it "should throw external errors nicely" #do 			
		# 	expect(@a.throw! "sample error text").to send_output({"item" => [
		# 		{
		# 			"title" => ["ERROR: sample error text"],
		# 			"subtitle" => ["See the log file below for debug info."]
		# 		}
		# 	]})
		# end
	end
end