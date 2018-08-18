# form-to-json
Serializes form data to json data.

# Usage

	<form onsubmit="return formToJson(this)">
		//form elements goes here
	</form>


# Sample JSON output

{
  "first_name": "Jhan",
  "middle_name": "Dgreat",
  "last_name": "Mateo",
  "date_of_birth": "12\/12\/2015",
  "address": {
    "addition_information": "Poca Village",
    "street_name": "69 Street",
    "po_box": "Sugbo",
    "country_name": "Philippines",
    "zip_code": "6000"
  },
  "skills": [
    {
      "name": "JavaScript",
      "rating": "P3"
    },
    {
      "name": "PHP",
      "rating": "P3"
    }
  ],
  "colors": [
    "red",
    "yellow",
    "blue"
  ],
  "survey": "yes",
  "gender": "M",
  "fruits": [
    "Mango",
    "Grapes"
  ],
  "message": "This is only optional message.",
  "browser": "Firefox",
  "range": "75",
  "points": "9",
  "file": "C:\\fakepath\\Jellyfish.jpg",
  "cars": "volvo"
}

