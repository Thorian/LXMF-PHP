<?php


class LXMF {
    // Constants
    const APP_NAME = "lxmf";

    /**
	 * @brief Core fields provided to facilitate interoperability in data exchange.
	 *
	 * These fields are used to enable data exchange between various LXMF clients and systems.
	 */
    const FIELD_EMBEDDED_LXMS = 0x01;
    const FIELD_TELEMETRY = 0x02;
    const FIELD_TELEMETRY_STREAM = 0x03;
    const FIELD_ICON_APPEARANCE = 0x04;
    const FIELD_FILE_ATTACHMENTS = 0x05;
    const FIELD_IMAGE = 0x06;
    const FIELD_AUDIO = 0x07;
    const FIELD_THREAD = 0x08;
    const FIELD_COMMANDS = 0x09;
    const FIELD_RESULTS = 0x0A;
    const FIELD_GROUP = 0x0B;
    const FIELD_TICKET = 0x0C;
    const FIELD_EVENT = 0x0D;
    const FIELD_RNR_REFS = 0x0E;
    const FIELD_RENDERER = 0x0F;

    /**
	 * @brief Custom fields for specialized use cases.
	 *
	 * These fields are intended for use cases such as including custom data structures,
	 * embedding or encapsulating other data types or protocols not native to LXMF,
	 * or bridging/tunneling external protocols or services over LXMF. The CUSTOM_TYPE
	 * field can include a format, type, protocol, or other identifier, while the
	 * embedded payload can be included in the CUSTOM_DATA field. It is up to the
	 * client application to correctly discern and potentially utilize any data
	 * embedded using this mechanism.
	 */
    const FIELD_CUSTOM_TYPE = 0xFB;
    const FIELD_CUSTOM_DATA = 0xFC;
    const FIELD_CUSTOM_META = 0xFD;

    // Non-specific and debug fields
    const FIELD_NON_SPECIFIC = 0xFE;
    const FIELD_DEBUG = 0xFF;
	
	
	/**
	 * @brief Field-specific specifiers, modes, and identifiers native to LXMF.
	 *
	 * This section lists field-specific specifiers, modes, and identifiers that are native to LXMF.
	 * It is optional for any client or system to support any of these specifiers and identifiers.
	 * They are provided as a template to ease interoperability without sacrificing the expandability
	 * and flexibility of the format.
	 */
    // Audio modes for the data structure in FIELD_AUDIO
	// Codec2 Audio Modes
    const AM_CODEC2_450PWB = 0x01;
    const AM_CODEC2_450 = 0x02;
    const AM_CODEC2_700C = 0x03;
    const AM_CODEC2_1200 = 0x04;
    const AM_CODEC2_1300 = 0x05;
    const AM_CODEC2_1400 = 0x06;
    const AM_CODEC2_1600 = 0x07;
    const AM_CODEC2_2400 = 0x08;
    const AM_CODEC2_3200 = 0x09;

	// Opus Audio Modes
    const AM_OPUS_OGG = 0x10;
    const AM_OPUS_LBW = 0x11;
    const AM_OPUS_MBW = 0x12;
    const AM_OPUS_PTT = 0x13;
    const AM_OPUS_RT_HDX = 0x14;
    const AM_OPUS_RT_FDX = 0x15;
    const AM_OPUS_STANDARD = 0x16;
    const AM_OPUS_HQ = 0x17;
    const AM_OPUS_BROADCAST = 0x18;
    const AM_OPUS_LOSSLESS = 0x19;

	// Custom, unspecified audio mode, the client must determine it itself based on the included data.
    const AM_CUSTOM = 0xFF;

    /**
	 * @brief Message renderer specifications for FIELD_RENDERER.
	 *
	 * The renderer specification is optional and serves as an indication to the receiving
	 * client on how to render the message contents. It is not mandatory to implement this
	 * specification on either the sending or receiving sides. However, it is recommended as
	 * a way to signal how to render a message when non-plaintext formatting is used.
	 */
    const RENDERER_PLAIN = 0x00;
    const RENDERER_MICRON = 0x01;
    const RENDERER_MARKDOWN = 0x02;
    const RENDERER_BBCODE = 0x03;

    // Helper methods
    public static function display_name_from_app_data($app_data = null) {
        if ($app_data === null || strlen($app_data) == 0) {
            return null;
        }

        $first_byte = ord($app_data[0]);
        if (($first_byte >= 0x90 && $first_byte <= 0x9f) || $first_byte == 0xdc) {
            $peer_data = msgpack_unpack($app_data);
            if (is_array($peer_data) && count($peer_data) >= 1) {
                $dn = $peer_data[0];
                if ($dn === null) {
                    return null;
                } else {
                    try {
                        $decoded = utf8_decode($dn);
                        return $decoded;
                    } catch (Exception $e) {
                        error_log("Could not decode display name: " . $e->getMessage());
                        return null;
                    }
                }
            }
        } else {
            return utf8_decode($app_data);
        }
    }

    public static function stamp_cost_from_app_data($app_data = null) {
        if ($app_data === null || $app_data === "") {
            return null;
        }

        $first_byte = ord($app_data[0]);
        if (($first_byte >= 0x90 && $first_byte <= 0x9f) || $first_byte == 0xdc) {
            $peer_data = msgpack_unpack($app_data);
            if (is_array($peer_data) && count($peer_data) >= 2) {
                return $peer_data[1];
            }
        }

        return null;
    }

    public static function pn_announce_data_is_valid($data) {
        try {
            if (is_string($data)) {
                $data = msgpack_unpack($data);
            }

            if (!is_array($data) || count($data) < 3) {
                throw new Exception("Invalid announce data: Insufficient peer data");
            }

            if ($data[0] !== true && $data[0] !== false) {
                throw new Exception("Invalid announce data: Indeterminate propagation node status");
            }

            if (!is_int($data[1])) {
                throw new Exception("Invalid announce data: Could not decode peer timebase");
            }
        } catch (Exception $e) {
            error_log("Announce data validation error: " . $e->getMessage());
            return false;
        }

        return true;
    }
}
