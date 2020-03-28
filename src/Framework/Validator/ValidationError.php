<?php

namespace Framework\Validator;

class ValidationError
{

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $key;

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $rule;

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $messages = [
        'required' => "Le champ %s est requis",
        'empty' => "Le champ %s ne peut-être vide",
        'slug' => "Le champ %s n\est pas un slug valide",
        'minLength' => "Le champ %s doit contenir plus de %d caractères",
        'maxLength' => "Le champ %s doit contenir moins de %d caractères",
        'betweenLength' => "Le champ %s doit contenir entre %d et %d caractères",
        'datetime' => "Le champ %s doit être une date valide %s",
        'exists' => "Le champ %s n'existe pas dans la table %s",
        'unique' => "Le champ %s doit être unique",
        'filetype' => "Le champ %s n'est pas au format valide (%s)",
        'uploaded' => "Vous devez uploaded un fichier"
    ];

    private $attributes;

    /**
     * ValidationError constructor.
     * @param string $key
     * @param string $rule
     * @param array $attributes
     */
    public function __construct(string $key, string $rule, $attributes = [])
    {
        $this->key = $key;
        $this->rule = $rule;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $params = array_merge(
            [
            $this->messages[$this->rule],
            $this->key
            ],
            $this->attributes
        );
        return (string)call_user_func_array('sprintf', $params);
    }
}
