toArray() as $key => $value) {
                $normalKey            = $this->normalizeKey((string)$key);
                $normalized[$normalKey] = $value;
            }
            $this->data[] = $normalized;
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    private function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        return trim($key, '_');
    }
}