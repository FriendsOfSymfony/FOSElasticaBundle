Attachments Handling
=======================

This is just a quick overview of how to handle attachment mappings, searching, and highlighting. 

Mappings
-----------------------------

You can set up the FOSElasticaBundle to use attachments in the mappings. An overview of the ElasticSearch attachment plugin
can be viewed here - https://github.com/elastic/elasticsearch-mapper-attachments. Installation instructions can be found
on the github page. If you want to highlight content from the document, then you will need to add `"store": true` AND 
`"term_vector":"with_positions_offsets"` to the attachment field.

> *Note*: Metadata mappings can be added as needed to the mappings file for attachments. More information can be seen
>       at the Github page for Elasicsearch mapper attachments.


```yaml
fos_elastica:
    indexes:
        app:
            types:
                user:
                    mappings:
                        id: ~
                        content:
                            type: attachment
                            path: full
                            fields:
                                name: { store: yes }
                                title: { store : yes }
                                date: { store : yes }
                                content : { term_vector: with_positions_offsets, store: yes }
                                ...
```


Attachment Searching
-----------------------------

Here is an example query that can be ran on attachments. This also includes an example of how to use the highlights functionality
 for attachments. 

```php
$keywordQuery = new QueryString();
$keywordQuery->addParam(NULL, array('fuzziness' => 1));
$keywordQuery->setQuery("$term~");
$keywordQuery->setDefaultOperator('AND');

$query = new Query($keywordQuery);
$query->setSource(array("id", "..."));
$query->setHighlight(array(
    'fields' => array(
        'content' => new \stdClass()
    )
));
```

Converting Attachments
-----------------------------

This is an example of indexing documents in the required base64 encoding. You will need to specify the upload directory of all 
 the attachments under the method getUploadDir(). The method getContent() contains the functionality to convert the file to
  base64.

```php
public function getContent()
{
    //Upload directory set at /web/uploads/library
    return base64_encode(file_get_contents($this->getUploadRootDir() . '/' . $this->filename, 'r'));
}

protected function getUploadRootDir()
{
    // the absolute directory path where uploaded
    // documents should be saved
    return __DIR__.'/../../../../web/'.$this->getUploadDir();
}

protected function getUploadDir()
{
    // get rid of the __DIR__ so it doesn't screw up
    // when displaying uploaded doc/image in the view.
    return 'uploads/library';
}
```

Handling Highlights
-----------------------------

If you want to grab highlights from your search query, it can be achieved by implementing the HighlightableModelInterface. 
The interface requires the getId() and the setElasticHighlights() method. You will also need the getElasticHighlights 
methods to view the output. An example entity is displayed below. 

```php
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;

class Library implements HighlightableModelInterface {

    private $id
    
    private $highlights;
    
    //Needs this method for HighlightableModelInterface
    public function getId()
    {
        return $this->id;
    }

    //Needs this method for HighlightableModelInterface
    public function setElasticHighlights(array $highlights)
    {
        $this->highlights = $highlights;

        return $this;
    }

    public function getElasticHighlights()
    {
        return $this->highlights;
    }

}
```

Viewing Highlights in Twig
-----------------------------

This is just a quick reference to obtaining the highlighted text returned by the query in a TWIG file.

```php
{% for highlights in reference.ElasticHighlights %}
    {% for highlight in highlights %}
        <tr class="alert alert-info">
            <td></td>
            <td></td>
            <td>{{ highlight|raw }}</td>
        </tr>
    {% endfor %}
{% endfor %}    
```
