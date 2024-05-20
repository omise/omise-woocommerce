import {useEffect, useRef} from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export const SavedCard = ({onChange, existingCards}) => {
    return(<>
		<h3>{ __('Use an existing card', 'omise') }</h3>
		<ul className="omise-customer-card-list">
			{existingCards.map((card, k) => {
				return <li key={card['id']} className="item">
					<input
						defaultChecked={k === 0}
						id={`card-${card['id']}`}
						type="radio"
						name="card_id"
						value={card['id']}
						onChange={onChange}
					/>
					<label htmlFor={`card-${card['id']}`}>
						<strong>{card['brand']}</strong> xxxx {card['last_digits']}
					</label>
				</li>
			})}
		</ul>
		<input id="new_card_info" type="radio" name="card_id" value="" onChange={onChange}/>
		<label id="label-new_card_info" htmlFor="new_card_info">
			<h3>{ __('Create a charge using new card', 'omise') }</h3>
		</label>
	</>)
}