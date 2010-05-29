# JoomGen

JoomGen is a code generator created by [JetWorks](http://jetworks.com.br/) to take out the pain of [Joomla!](http://www.joomla.org/) component development. It takes a small set of from [YAML](http://en.wikipedia.org/wiki/YAML) or [UML](http://en.wikipedia.org/wiki/Unified_Modeling_Language) files and generates a complete Joomla! component ready to install and use.

The component's code follows the [PEAR coding conventions](http://pear.php.net/manual/en/coding-standards.php) and many Joomla! best practices.

## Usage (YAML)

### component.yaml

This file will contain general data about your component. In this file you will see something like this:

<pre>
  name: Jobline
  identifier: Jobline
  component: com_jobline
  version: 1.0.0
  author:
      name: JetWorks
      email: contact@jetworks.com.br
      url: http://jetworks.com.br
  copyright: 2010 JetWorks. All rights reserved.
  license: GNU General Public License
  description: Component description
  default_language: en-GB
  database:
      engine: MyISAM
      default_charset: utf8
</pre>

Most of these are self-explanatory. The fields you need to change in every project are:

* name: the project's name, like "My Project"
* identifier: a class name based on the project name, like "MyProject"
* component: the component's name, like "com_myproject"
* version
* author

### models.yaml

This file will contain data about the models / tables used in your component. The syntax is the following:

<pre>
  model_name:
      field_name: type
  
  another_model_name:
      not_required_field:
          type: type
          required: false
      field_with_description:
          type: type
          description: This is my field

  sql_table_without_model_name:
      sql_only: true
</pre>

Some rules about the MVC component generation:

* All tables get an integer primary key named "id" automatically;
* If "sql_only" is true then just the SQL table will be generated (without the MVC part); this is useful when two models have a "Many To Many" relationship table, for example;
* If a model has "published: bool", publication / unpublication logic is added to the admin interface;
* Models names should be in plural form.

An example models.yaml file:

<pre>
  posts:
      title: string
      content: rich_text
      created_on: datetime
  
  tags:
      name: string
  
  posts_tags:
      post_id: int
      tag_id: int
      sql_only: true
</pre>

### frontend.yaml

This file will contain data about the frontend views for each model used in your component. The syntax is the following:

<pre>
  model_name:
      details: field names separated by space
      new: field names separated by space
      list: field names separated by space
  
  another_model_name:
      details: field names separated by space
      new: field names separated by space
      list: field names separated by space
</pre>

These field names are the fields from your models that you want to display in your views. You only need to add entries for the models you want to use in the frontend.

An example frontend.yaml file:

<pre>
  posts:
      details: title content created_on
      new: title content
      list: title content created_on
</pre>


#### Field types / SQL respectives

* string: VARCHAR(255)
* text: TEXT
* int: INT(11)
* datetime: DATETIME
* date: DATE
* time: TIME
* double: DOUBLE
* decimal: DECIMAL
* rich_text: TEXT
* bool: INT(1)

#### Field attributes

* type: don't have a default value
* required: defaults to "true"
* description: defaults to a capitalized version of the field name. "contact_name" became "Contact Name"

