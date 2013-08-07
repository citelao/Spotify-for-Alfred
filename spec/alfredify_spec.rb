require_relative '../alfredify'

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

			expect @a.output!.should == "<?xml version=\"1.0\"?>\\r\\n\\t<items>\\r\\n\t\t<item uid='uid' arg='arg' valid='yes' autocomplete='autocomplete'>\r\n\t\t\t<title>title</title>\r\n\t\t\t<subtitle>subtitle</subtitle>\r\n\t\t\t<icon>icon</icon>\r\n\t\t</item>\r\n\t</items>"
		end

		it "should require valid to be boolean" do
			expect { @a.add :valid => 'dumb' }.to raise_error "invalid argument for 'valid'"
		end

		it "should require autocomplete if invalid" do
			expect { @a.add :valid => 'no' }.to raise_error "'autocomplete' is required when 'valid' is 'no'"
		end
	end

	context "when outputting" do
		it "should output alfred xml" do
			@a.add

			expect @a.output!.should == 
				"<?xml version=\"1.0\"?>\\r\\n\\t<items>\\r\\n\t\t<item>\r\n\t\t\t<title>null</title>\r\n\t\t</item>\r\n\t</items>"
		end

		it "should allow multiple items"

		it "should throw internal errors nicely"
		it "should throw external errors nicely"
	end
end