<?php

namespace App\Traits;

/**
 * Trait para alternar el estado de activación de cualquier modelo.
 */
trait HasActiveToggle
{
    /**
     * Alterna el estado del campo 'active' del modelo.
     *
     * @param string $field Campo a alternar (por defecto 'active')
     * @return self
     */
    public function toggle(string $field = 'active'): self
    {
        if (!array_key_exists($field, $this->attributes)) {
            throw new \InvalidArgumentException("El campo '$field' no existe en el modelo " . get_class($this));
        }

        $this->{$field} = !$this->{$field};
        $this->save();

        return $this;
    }

    /**
     * Activa el modelo estableciendo el campo especificado como true.
     *
     * @param string $field Campo a activar (por defecto 'active')
     * @return self
     */
    public function activate(string $field = 'active'): self
    {
        if (!array_key_exists($field, $this->attributes)) {
            throw new \InvalidArgumentException("El campo '$field' no existe en el modelo " . get_class($this));
        }

        $this->{$field} = true;
        $this->save();

        return $this;
    }

    /**
     * Desactiva el modelo estableciendo el campo especificado como false.
     *
     * @param string $field Campo a desactivar (por defecto 'active')
     * @return self
     */
    public function deactivate(string $field = 'active'): self
    {
        if (!array_key_exists($field, $this->attributes)) {
            throw new \InvalidArgumentException("El campo '$field' no existe en el modelo " . get_class($this));
        }

        $this->{$field} = false;
        $this->save();

        return $this;
    }
}
