<?php

namespace Tsquare\FileGenerator;

use PHPUnit\Framework\TestCase;

class EditorTest extends TestCase
{
    protected Template $template;

    public function setUp(): void
    {
        $this->template = FileTemplate::init(__DIR__ . '/Templates/Foo.php');
    }

    public function tearDown(): void
    {
        unlink(__DIR__ . '/Fixtures/Foo.php');

        $template = FileTemplate::init(__DIR__ . '/Templates/Foo.php');

        $generator = new FileGenerator($template);

        $generator->create();
    }

    /** @test */
    public function can_insert_before(): void
    {
        $editor = new FileEditor();

        $editor->insertBefore('
    public function get{name}(): {name}
    {
        return $this;
    }

', '    public function {underscore}');

        $this->template->ifFileExists($editor);

        $generator = new FileGenerator($this->template);

        $generator->create();

        $fileContents = file_get_contents(__DIR__ . '/Fixtures/Foo.php');

        $this->assertStringContainsString('
    public function getFoo(): Foo
    {
        return $this;
    }

    public function foo(): Foo
    {
        return $this;
    }
', $fileContents);
    }

    /** @test */
    public function can_insert_after(): void
    {
        $editor = new FileEditor();

        $editor->insertAfter('
    public function set{name}(): {name}
    {
        return $this;
    }
', 'public function {underscore}(): {name}
    {
        return $this;
    }
');

        $this->template->ifFileExists($editor);

        $generator = new FileGenerator($this->template);

        $generator->create();

        $fileContents = file_get_contents(__DIR__ . '/Fixtures/Foo.php');

        $this->assertStringContainsString('
    public function foo(): Foo
    {
        return $this;
    }

    public function setFoo(): Foo
    {
        return $this;
    }
', $fileContents);
    }

    /** @test */
    public function can_replace_text(): void
    {
        $editor = new FileEditor();

        $editor->replace('{underscore}', 'bar');

        $this->template->ifFileExists($editor);

        $generator = new FileGenerator($this->template);

        $generator->create();

        $fileContents = file_get_contents(__DIR__ . '/Fixtures/Foo.php');

        $this->assertStringContainsString('public function bar()', $fileContents);
    }

    /** @test */
    public function can_insert_before_text_or_other_text(): void
    {
        $editor = new FileEditor();

        $editor->insertBefore('
    public function get{name}(): {name}
    {
        return $this;
    }

', 'some non-existent text', [
            'before' => '    public function {underscore}',
        ]);

        $this->template->ifFileExists($editor);

        $generator = new FileGenerator($this->template);

        $generator->create();

        $fileContents = file_get_contents(__DIR__ . '/Fixtures/Foo.php');

        $this->assertStringContainsString('
    public function getFoo(): Foo
    {
        return $this;
    }

    public function foo(): Foo
    {
        return $this;
    }
', $fileContents);
    }

    /** @test */
    public function can_insert_after_text_or_other_text(): void
    {
        $editor = new FileEditor();

        $editor->insertAfter('
    public function bar{name}(): {name}
    {
        return $this;
    }
', 'public function {underscore}Method(): {name}
    {
        return $this;
    }
', [
    'after' => 'public function {underscore}(): {name}
    {
        return $this;
    }
'
        ]);

        $this->template->ifFileExists($editor);

        $generator = new FileGenerator($this->template);

        $generator->create();

        $fileContents = file_get_contents(__DIR__ . '/Fixtures/Foo.php');

        $this->assertStringContainsString('
    public function foo(): Foo
    {
        return $this;
    }

    public function barFoo(): Foo
    {
        return $this;
    }
', $fileContents);
    }

    /** @test */
    public function can_replace_text_or_other_text(): void
    {
        $editor = new FileEditor();

        $editor->replace('{underscore}nonexistent', 'bar_test', ['replace' => '{underscore}']);

        $this->template->ifFileExists($editor);

        $generator = new FileGenerator($this->template);

        $generator->create();

        $fileContents = file_get_contents(__DIR__ . '/Fixtures/Foo.php');

        $this->assertStringContainsString('public function bar_test()', $fileContents);
    }

    /** @test */
    public function doesnt_insert_if_contains_string_in_if_not_contains(): void
    {
        $editor = new FileEditor();

        $editor->replace('{underscore}', 'bar')->ifNotContaining('{underscore}');

        $this->template->ifFileExists($editor);

        $generator = new FileGenerator($this->template);

        $generator->create();

        $fileContents = file_get_contents(__DIR__ . '/Fixtures/Foo.php');

        $this->assertStringNotContainsString('bar', $fileContents);
    }
}
