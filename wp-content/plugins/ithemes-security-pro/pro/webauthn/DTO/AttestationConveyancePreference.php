<?php

namespace iThemesSecurity\WebAuthn\DTO;

final class AttestationConveyancePreference {

	/**
	 * The Relying Party is not interested in authenticator attestation.
	 * For example, in order to potentially avoid having to obtain user
	 * consent to relay identifying information to the Relying Party, or
	 * to save a roundtrip to an Attestation CA or Anonymization CA.
	 *
	 * If the authenticator generates an attestation statement that is not
	 * a self attestation, the client will replace it with a None attestation.
	 */
	const NONE = 'none';

	/**
	 * The Relying Party wants to receive a verifiable attestation statement,
	 * but allows the client to decide how to obtain such an attestation statement.
	 *
	 * The client MAY replace an authenticator-generated attestation statement with
	 * one generated by an Anonymization CA, in order to protect the user’s privacy,
	 * or to assist Relying Parties with attestation verification in a heterogeneous
	 * ecosystem.
	 */
	const INDIRECT = 'indirect';

	/**
	 * The Relying Party wants to receive the attestation statement as generated
	 * by the authenticator.
	 */
	const DIRECT = 'direct';

	/**
	 * The Relying Party wants to receive an attestation statement that may include
	 * uniquely identifying information. This is intended for controlled deployments
	 * within an enterprise where the organization wishes to tie registrations to
	 * specific authenticators
	 */
	const ENTERPRISE = 'enterprise';

	const ALL = [
		self::NONE,
		self::INDIRECT,
		self::DIRECT,
		self::ENTERPRISE,
	];
}
