using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using System;
using UnityEngine.UI;

public class dadut : MonoBehaviour{
	public GUISkin skin;
	public int dadunumber;
	public pausemenu aa;
	public Vector3[] posisidadu;
	public bool sa = false;
	int a,c,m,x,hasil,hasil2;
	public int Mulaihitung;
	public GameObject objekdadu;
	public Transform canvas;
	public FontStyle fontstyle;
	public Texture icon;
	public Texture icon2;
	public Texture turncomputer;
	public PLAYER player1;
	public PLAYER computer;
	private AudioSource dadu1;
	public AudioClip dadu1sound;
	private AudioSource dadu2;
	public AudioClip dadu2sound;
	private AudioSource dadu3;
	public AudioClip dadu3sound;
	private AudioSource dadu4;
	public AudioClip dadu4sound;
	private AudioSource dadu5;
	public AudioClip dadu5sound;
	private AudioSource dadu6;
	public AudioClip dadu6sound;
	public bool extend = true;
	public GameObject btnend;
	public TimeSpan startdatel;
	public Button rolldadut;
	public Button lempar;
	public Button endturn;
	public GameObject closed;
	public GameObject rollshow;
	public GameObject lemparshow;
	public GameObject endturnshow;
	// Use this for initialization
	void Start () {
		
		a = 5;
		c = 7;
		m = 7;
		x = 5;
	}
		void Awake () {


			dadu1 = GetComponent<AudioSource>();
		dadu2 = GetComponent<AudioSource>();
		dadu3 = GetComponent<AudioSource>();
		dadu4 = GetComponent<AudioSource>();
		dadu5 = GetComponent<AudioSource>();
		dadu6 = GetComponent<AudioSource>();

	}
	// Update is called once per frame
	void Update () {
		
		if (Mulaihitung == 1) {
			objekdadu.transform.Rotate (15, 15, 15);

		}

	}

	void SetdaduPos(int aValue){
		Quaternion temp = objekdadu.transform.rotation;
		temp.eulerAngles = posisidadu [aValue];
		objekdadu.transform.rotation = temp;
	
	}
	void SetdaduPos2(int aValue){
		Quaternion temp = objekdadu.transform.rotation;
		temp.eulerAngles = posisidadu [aValue];
		objekdadu.transform.rotation = temp;

	}
	IEnumerator waktu_turnplayer(){
		
		yield return new WaitForSeconds (2);
		player1.Execredplayermove ();
		yield return new WaitForSeconds (2);

	}

	IEnumerator ko(){
		yield return new WaitForSeconds (3);
		Debug.Log ("tessss");
	}
	IEnumerator waktucomputer(){

		yield return new WaitForSeconds (2);

		computer.Execblueplayermove ();
	}

	IEnumerator waktu11(){

		yield return new WaitForSeconds (2);

		GUI.enabled = false;
	}
	public void buttonroll(){
		
		Mulaihitung = 1;
		lemparshow.SetActive (true);
		rollshow.SetActive (false);
	}
	public void kop(){
		
	//	TimeSpan span = DateTime.Now.Subtract (new DateTime (2017,07,17));
	//x = (int)span.TotalSeconds;
	
	


		for (int i = 0; i < 7; i++) {
			x = (a * x + c) % m;
			hasil = x % 7;

			if (i < 7) {
				dadunumber = hasil;

				SetdaduPos (hasil);
				if (dadunumber == 0) {
					dadunumber = 2;
				}


			}
		
			StartCoroutine (waktu_turnplayer ());
			rollshow.SetActive (true);
			lemparshow.SetActive (false);
			Mulaihitung = 2;

			i = 7;

			if (dadunumber == 1) {
				dadu1.PlayOneShot (dadu1sound);
			}
			if (dadunumber == 2) {
				dadu2.PlayOneShot (dadu2sound);
			}
			if (dadunumber == 3) {
				dadu3.PlayOneShot (dadu3sound);
			}
			if (dadunumber == 4) {
				dadu4.PlayOneShot (dadu4sound);
			}
			if (dadunumber == 5) {
				dadu5.PlayOneShot (dadu5sound);
			}
			if (dadunumber == 6) {
				dadu6.PlayOneShot (dadu6sound);
			}
			Debug.Log ("hasil adalah " + hasil);
		}
		if (Mulaihitung == 2) {
			Mulaihitung = 3;
			rollshow.SetActive (false);
			lemparshow.SetActive (false);
			StartCoroutine (delayendturn ());

		}
		}

	IEnumerator delayendturn(){
		yield return new WaitForSeconds (4);
		endturnshow.SetActive (true);
	}

	public void rolldadu(){

		if (Mulaihitung == 0) {


			Button btn = rolldadut.GetComponent<Button>();
			btn.onClick.AddListener(buttonroll);

		}

		if (Mulaihitung == 1) {
			Button btnlempar = lempar.GetComponent<Button>();
			btnlempar.onClick.AddListener(kop);
			lemparshow.SetActive (false);
			rollshow.SetActive (true);
		}


	}
	IEnumerator delayturncomputer(){
		yield return new WaitForSeconds (2);
		//TimeSpan span = DateTime.Now.Subtract (new DateTime (1970, 1, 1, 0, 0, 0));
		//x = (int)span.TotalSeconds;
		for (int i = 0; i < 7; i++) {
			x = (a * x + c) % m;
			hasil = x % 7;

			if (i < 7) {
				dadunumber = hasil;
				StartCoroutine (waktucomputer ());

				SetdaduPos (hasil);
				if (dadunumber == 0) {
					dadunumber = 2;
				}


			}
			i = 7;

			if (dadunumber == 1) {
				dadu1.PlayOneShot (dadu1sound);
			}
			if (dadunumber == 2) {
				dadu2.PlayOneShot (dadu2sound);
			}
			if (dadunumber == 3) {
				dadu3.PlayOneShot (dadu3sound);
			}
			if (dadunumber == 4) {
				dadu4.PlayOneShot (dadu4sound);
			}
			if (dadunumber == 5) {
				dadu5.PlayOneShot (dadu5sound);
			}
			if (dadunumber == 6) {
				dadu6.PlayOneShot (dadu6sound);
			}
			Debug.Log ("hasil adalah " + hasil);
		}
	}
	public void endturnmethod(){
		StartCoroutine (delayturncomputer ());
		endturnshow.SetActive (false);
		StartCoroutine (delayafterendturn ());
	}

	IEnumerator delayafterendturn(){
		yield return new WaitForSeconds (6);
		lemparshow.SetActive (false);
		rollshow.SetActive (true);

	}

	public void rolldaducomputer(){
		
			Mulaihitung = 3;


		if (Mulaihitung == 3) {
			Button btnlempar = endturn.GetComponent<Button>();
			btnlempar.onClick.AddListener(endturnmethod);


			}
		
				
			
		}


	
}
	





