<?php

namespace Swf\Cli\Export;

use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;
use Swf\Cli\Jar;

/**
 * The FFDEC -export command
 *
 * Export <infile_or_directory> sources to <outdirectory>.
 *
 * <code>
 * $result = $export->itemTypes([Export::ITEM_TYPE_SCRIPT])->input('my.swf')->ids(56)->execute();
 * </code>
 */
final class Export
{
    /** Scripts (Default format: ActionScript source) */
    const ITEM_TYPE_SCRIPT = 'script';
    /** Images (Default format: PNG/JPEG) */
    const ITEM_TYPE_IMAGE = 'image';
    /** Shapes (Default format: SVG) */
    const ITEM_TYPE_SHAPE = 'shape';
    /** MorphShapes (Default format: SVG) */
    const ITEM_TYPE_MORPHSHAPE = 'morphshape';
    /** Movies (Default format: FLV without sound) */
    const ITEM_TYPE_MOVIE = 'movie';
    /** Fonts (Default format: TTF) */
    const ITEM_TYPE_FONT = 'font';
    /** Frames (Default format: PNG) */
    const ITEM_TYPE_FRAME = 'frame';
    /** Sprites (Default format: PNG) */
    const ITEM_TYPE_SPRITE = 'sprite';
    /** Buttons (Default format: PNG) */
    const ITEM_TYPE_BUTTON = 'button';
    /** Sounds (Default format: MP3/WAV/FLV only sound) */
    const ITEM_TYPE_SOUND = 'sound';
    /** Binary data (Default format:  Raw data) */
    const ITEM_TYPE_BINARY_DATA = 'binaryData';
    /** Texts (Default format: Plain text) */
    const ITEM_TYPE_TEXT = 'text';
    /** Every resource (but not FLA and XFL) */
    const ITEM_TYPE_ALL = 'all';
    /** Everything to FLA compressed format */
    const ITEM_TYPE_FLA = 'fla';
    /** Everything to uncompressed FLA format (XFL) */
    const ITEM_TYPE_XFL = 'xfl';

    /** ActionScript source */
    const FORMAT_AS = 'as';
    /** ActionScript P-code */
    const FORMAT_PCODE = 'pcode';
    /** ActionScript P-code with hex */
    const FORMAT_PCODEHEX = 'pcodehex';
    /** ActionScript Hex only */
    const FORMAT_HEX = 'hex';
    /** SVG format for Shapes, MorphShape, Frame, Sprites, Buttons and Images */
    const FORMAT_SVG = 'svg';
    /** PNG format for Shapes, MorphShape, Frame, Sprites, Buttons and Images */
    const FORMAT_PNG = 'png';
    /** HTML5 Canvas format for Shapes, MorphShape, Frame, Sprites, Buttons and Images */
    const FORMAT_CANVAS = 'canvas';
    /** BMP format for Shapes, MorphShape, Frame, Sprites, Buttons and Images */
    const FORMAT_BMP = 'bmp';
    /** GIF format for Frames, Sprites */
    const FORMAT_GIF = 'gif';
    /** AVI format for Frames, Sprites */
    const FORMAT_AVI = 'avi';
    /** PDF format for Frames, Sprites */
    const FORMAT_PDF = 'pdf';
    /** PNG/GIF/JPEG format for Images */
    const FORMAT_PNG_GIF_JPEG = 'png_gif_jpeg';
    /** JPEG format for Images */
    const FORMAT_JPEG = 'jpeg';
    /** Plain text format for Texts */
    const FORMAT_PLAIN = 'plain';
    /** Formatted text format for Texts */
    const FORMAT_FORMATTED = 'formatted';
    /** MP3/WAV/FLV format for Sounds */
    const FORMAT_MP3_WAV_FLV = 'mp3_wav_flv';
    /** MP3/WAV format for Sounds */
    const FORMAT_MP3_WAV = 'mp3_wav';
    /** WAV format for Sounds */
    const FORMAT_WAV = 'wav';
    /** FLV format for Sounds */
    const FORMAT_FLV = 'flv';
    /** TTF format for Fonts */
    const FORMAT_TTF = 'ttf';
    /** WOFF format for Fonts */
    const FORMAT_WOFF = 'woff';


    /**
     * @var Jar
     */
    private $jar;

    /**
     * List of item types to export
     *
     * @var string[]
     */
    private $itemTypes = [];

    /**
     * The frames/pages range to export
     *
     * @var array
     */
    private $select = [];

    /**
     * The characters ids to export
     *
     * @var array
     */
    private $ids = [];

    /**
     * The input swf file, or directory
     *
     * @var string
     */
    private $input;

    /**
     * The output directory
     * If null, will creates a temporary directory
     *
     * @var string|null
     */
    private $output = null;


    /**
     * Export constructor.
     *
     * @param Jar $jar The FFDEC jar
     */
    public function __construct(Jar $jar)
    {
        $this->jar = $jar;
    }

    /**
     * The input file or directory to export
     *
     * @param string $fileOrDirectory
     *
     * @return $this
     */
    public function input(string $fileOrDirectory): self
    {
        $this->input = $fileOrDirectory;

        return $this;
    }

    /**
     * Set the output directory
     *
     * @param string $outputDirectory
     *
     * @return $this
     */
    public function output(string $outputDirectory): self
    {
        $this->output = $outputDirectory;

        return $this;
    }

    /**
     * Add item types to export
     *
     * See Export::ITEM_TYPE_* constants for the list of available types
     *     Export::FORMAT_* for list of available formats
     *
     * <code>
     * // Exports script and images, with default format
     * $export->itemTypes([Export::ITEM_TYPE_SCRIPT, Export::ITEM_TYPE_IMAGE])->execute();
     *
     * // Exports script with "pcode" format, and images with default format
     * $export->itemTypes([
     *     Export::ITEM_TYPE_SCRIPT => Export::FORMAT_PCODE,
     *     Export::ITEM_TYPE_IMAGE
     * ])->execute();
     * </code>
     *
     * @param string[] $types Array of item types. For specify a format, set the type as key, and format as value
     *
     * @return $this
     */
    public function itemTypes(array $types): self
    {
        foreach ($types as $type => $format) {
            if (is_int($type)) {
                $type = $format;
                $format = null;
            }

            $this->itemTypes[$type] = $format;
        }

        return $this;
    }

    /**
     * Add frames/pages range to export
     *
     * Available synopsis :
     * select (int $from, int $to = null): $this - Select frames in range [$from, $to] If $to is null, will only select the $from frame
     * select (string $range): $this             - Specify a raw range in string (formats: "from-to"|"frame"|"start-")
     * select (array $ranges): $this             - Specify multiple range, each elements follow the string range format
     *
     * <code>
     * $export->select(5); // Select the 5th frame
     * $export->select(5, 7); // Select range [5, 7]
     * $export->select('5-7'); // Same as above
     * $export->select([4, 7, '9-12']); // Select frames 4, 7, and 9 to 12
     * </code>
     *
     * @param string|int|array $from The range start, or range specifier
     * @param int|null $to The end of the range (only if $from is an int)
     *
     * @return $this
     */
    public function select($from, int $to = null): self
    {
        if (is_int($from)) {
            if ($to !== null) {
                $this->select[] = $from . '-' . $to;
            } else {
                $this->select[] = $from;
            }

            return $this;
        }

        if ($to !== null) {
            throw new InvalidArgumentException('$to is invalid when $from is not an integer');
        }

        if (is_array($from)) {
            $this->select = array_merge($this->select, $from);
        } else {
            $this->select[] = $from;
        }

        return $this;
    }

    /**
     * Add characters ids to select for export
     *
     * Available synopsis :
     * ids (int $id): $this            - Select $id
     * ids (int $from, int $to): $this - Select ids in range [$from, $to]
     * ids (string $range): $this      - Specify a raw range in string (formats: "from-to"|"frame"|"start-")
     * ids (array $ranges): $this      - Specify multiple range, each elements follow the string range format
     *
     * <code>
     * $export->ids(5); // Select the id 5
     * $export->ids(5, 7); // Select range [5, 7]
     * $export->ids('5-7'); // Same as above
     * $export->ids([4, 7, '9-12']); // Select ids 4, 7, and 9 to 12
     * </code>
     *
     * @param string|int|array $from The range start, or range specifier
     * @param int|null $to The end of the range (only if $from is an int)
     *
     * @return $this
     */
    public function ids($from, int $to = null): self
    {
        if (is_int($from)) {
            if ($to !== null) {
                $this->ids[] = $from . '-' . $to;
            } else {
                $this->ids[] = $from;
            }

            return $this;
        }

        if ($to !== null) {
            throw new InvalidArgumentException('$to is invalid when $from is not an integer');
        }

        if (is_array($from)) {
            $this->ids = array_merge($this->ids, $from);
        } else {
            $this->ids[] = $from;
        }

        return $this;
    }

    /**
     * Execute the export command
     *
     * @return ExportResult
     */
    public function execute(): ExportResult
    {
        if (empty($this->input)) {
            throw new BadMethodCallException('Missing input file or directory');
        }

        $output = $this->outputDirectory();

        $jar = $this->applyFormats($this->jar);
        $jar = $this->applyRanges($jar);

        $jar
            ->option('onerror', 'ignore') // @todo configure ?
            ->option('export', implode(array_keys($this->itemTypes)))
            ->argument($output)
            ->argument($this->input)
            ->execute()
        ;

        return new ExportResult($output);
    }

    /**
     * Apply the format option to the command
     *
     * @param Jar $jar
     *
     * @return Jar
     */
    private function applyFormats(Jar $jar): Jar
    {
        $formats = [];

        foreach ($this->itemTypes as $type => $format) {
            if ($format !== null) {
                $formats[] = $type.':'.$format;
            }
        }

        if (empty($formats)) {
            return $jar;
        }

        return $jar->option('format', implode(',', $formats));
    }

    private function applyRanges(Jar $jar): Jar
    {
        if ($this->select) {
            $jar = $jar->option('select', implode(',', $this->select));
        }

        if ($this->ids) {
            $jar = $jar->option('selectid', implode(',', $this->ids));
        }

        return $jar;
    }

    /**
     * Get the output directory
     *
     * @return string
     */
    private function outputDirectory(): string
    {
        if ($this->output) {
            return $this->output;
        }

        $tmp = sys_get_temp_dir();
        $attempts = 10;

        do {
            if ($attempts-- === 0) {
                throw new RuntimeException('Cannot create a temporary directory.');
            }

            $dir = $tmp . DIRECTORY_SEPARATOR . 'swf_' . bin2hex(random_bytes(8));
        } while (!mkdir($dir, 0700));

        return $dir;
    }
}
