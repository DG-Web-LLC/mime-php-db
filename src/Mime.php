<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb;

use DGWebLLC\MimePhpDb\Exception\Mime\DeserializationError;
use DGWebLLC\MimePhpDb\Exception\Mime\ItemNotEqual;

/**
 * Creates a mime object from ether a serialized string or an array of named parameters
 * 
 * To ensure consistency this about should serialized using the implicit toString operator and
 * deserialized using the constructor.
 * 
 * Examples:
 * 
 *  // Initializes a Mime Object using a parameter array
 * 
 *  $mime = new Mime([
 *      'name' => 'type/subtype',
 *      'source' => ['custom',]
 *      'extensions' => ['.ext',]
 *  ]);
 *  
 *  // Serializes and Deserializes the original mime object using the implicit toString and constructor.
 * 
 *  $cpy = new Mime((string)$mime);
 * 
 *  // Prints the two objects using the implicit toString operator.
 * 
 *  echo $mime."\n".$cpy."\n";
 */
class Mime {
    /**
     * The Media Type Name, normally expressed in type/subtype format
     * @var string
     */
    public string $name = "";
    /**
     * An array of sources, this identifies where the data was retrieved from
     * @var string[]
     */
    public array $source = [];
    /**
     * An array of extensions, this identifies the expected file extension for the media type
     * @var string[]
     */
    public array $extensions = [];
    /**
     * Creates a mime object from ether a serialized string or an array of named parameters
     * 
     * To ensure consistency this about should serialized using the implicit toString operator and
     * deserialized using the constructor.
     * 
     * @param array|string $params
     */
    public function __construct(array|string $params) {
        if ( is_array($params) ) {
            $this->name = $params['name'] ?? $this->name;
            $this->source = $params['source'] ?? $this->source;
            $this->extensions = $params['extensions'] ?? $this->extensions;
        } else {
            $arr = explode("\t", $params);

            if (count($arr) != 3) {
                throw new DeserializationError("Could not deserialize provided string");
            }

            $this->name = $arr[0];
            $this->source = explode(",", $arr[1]);
            $this->extensions = explode(",", $arr[2]);
        }
    }
    public static function __set_state(array $properties) { return new self($properties); }
    /**
     * Returns the object as tab delimited string, with comma delimited sub-strings. This is
     * for a consentient data serialization and deserialization.
     * 
     * Format: 'name'\t['source',]\t['ext',]
     * 
     * Example: 'text/html   iana    htm,html'
     * @return string
     */
    public function __toString(): string {
        $this->source = array_values(array_filter($this->source));
        $this->extensions = array_values(array_filter($this->extensions));

        return $this->name."\t".
               implode(",", $this->source)."\t".
               implode(",", $this->extensions);
    }
    /**
     * Merges the values of two Mime Objects if the names are equal
     * 
     * @param \DGWebLLC\MimePhpDb\Mime $mime The Object to merge
     * @throws \DGWebLLC\MimePhpDb\Exception\Mime\ItemNotEqual If the names do not match
     * @return void
     */
    public function merge(self $mime) {
        if (strtolower($this->name) != strtolower($mime->name)) {
            throw new ItemNotEqual("Mime name must match!");
        }

        $this->extensions = array_values(
            array_filter(
                array_unique(
                    array_merge(
                        $this->extensions ?? [],
                        $mime->extensions ?? []
                    )
                )
            )
        );
        $this->source = array_values(
            array_filter(
                array_unique(
                    array_merge(
                        $this->source ?? [],
                        $mime->source ?? []
                    )
                )
            )
        );
    }
}